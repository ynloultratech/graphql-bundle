<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Plugin;

use GraphQL\Type\Definition\ObjectType;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\EnumDefinition;
use Ynlo\GraphQLBundle\Definition\EnumValueDefinition;
use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\DependencyInjection\BackwardCompatibilityAwareInterface;
use Ynlo\GraphQLBundle\DependencyInjection\BackwardCompatibilityAwareTrait;
use Ynlo\GraphQLBundle\Filter\FilterFactory;
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\OrderBy\Common\OrderByRelatedField;
use Ynlo\GraphQLBundle\OrderBy\Common\OrderBySimpleField;
use Ynlo\GraphQLBundle\OrderBy\OrderByInterface;
use Ynlo\GraphQLBundle\Query\Node\AllNodesWithPagination;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;
use Ynlo\GraphQLBundle\Util\FieldOptionsHelper;

/**
 * Convert a simple return of nodes into a paginated collection with edges
 */
class PaginationDefinitionPlugin extends AbstractDefinitionPlugin implements BackwardCompatibilityAwareInterface
{
    use BackwardCompatibilityAwareTrait;

    public const ONE_TO_MANY = 'ONE_TO_MANY';
    public const MANY_TO_MANY = 'MANY_TO_MANY';

    /**
     * @var FilterFactory
     */
    protected $filterFactory;

    /**
     * @var int
     */
    protected $limit;

    /**
     * PaginationDefinitionPlugin constructor.
     *
     * @param FilterFactory $filterFactory
     * @param array         $config
     */
    public function __construct(FilterFactory $filterFactory, array $config = [])
    {
        $this->filterFactory = $filterFactory;
        $this->limit = $config['limit'] ?? 100;
    }

    /**
     * {@inheritDoc}
     */
    public function buildConfig(ArrayNodeDefinition $root): void
    {
        $config = $root
            ->info('Enable pagination in queries or sub-fields')
            ->canBeEnabled()
            ->children();

        /** @var NodeBuilder $rootNode */
        $config->scalarNode('target')
               ->info('Target node to properly paginate. If is possible will be auto-resolved using naming conventions')
               ->isRequired();
        $config->variableNode('filters')
               ->info('Filters configuration');
        $config->variableNode('order_by');
        $config->variableNode('search_fields');
        $config->integerNode('limit')->info('Max number of records allowed for first & last')->defaultValue($this->limit);
        $config->scalarNode('parent_field')
               ->info('When is used in sub-fields should be the field to filter by parent instance');
        $config->enumNode('parent_relation')
               ->info('When is used in sub-fields should be the type of relation with the parent field')
               ->defaultValue(self::ONE_TO_MANY)
               ->values([self::ONE_TO_MANY, self::MANY_TO_MANY]);
    }

    /**
     * {@inheritDoc}
     */
    public function normalizeConfig(DefinitionInterface $definition, $config): array
    {
        if (true === $config && $definition instanceof ExecutableDefinitionInterface) {
            $config = [];
        }

        if (\is_array($config) && !isset($config['target'])) {
            $config['target'] = $definition->getType();
        }

        if (false === $config) {
            $config = [];
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DefinitionInterface $definition, Endpoint $endpoint, array $config): void
    {
        if (!$config) {
            return;
        }

        if (!$definition instanceof QueryDefinition && !$definition instanceof FieldDefinition) {
            return;
        }

        $target = null;
        if ($definition instanceof FieldDefinition) {
            $target = $definition->getType();
            // only apply pagination to inherited fields
            // if all interfaces has pagination enabled
            if ($definition->getInheritedFrom()) {
                foreach ($definition->getInheritedFrom() as $inheritedType) {
                    /** @var InterfaceDefinition $inheritedDefinition */
                    $inheritedDefinition = $endpoint->getType($inheritedType);
                    if (!$inheritedDefinition->getField($definition->getName())->hasMeta('pagination')) {
                        return;
                    }
                }
            }
        }

        $search = new ArgumentDefinition();
        $search->setName('search');
        $search->setType('string');
        $search->setNonNull(false);
        $search->setDescription('Search in current list by given string');
        $definition->addArgument($search);

        $target = $config['target'] ?? $target;
        if ($endpoint->hasTypeForClass($target)) {
            $target = $endpoint->getTypeForClass($target);
        }
        $targetNode = $endpoint->getType($target);

        $this->addPaginationArguments($definition);
        $this->createOrderBy($endpoint, $definition, $targetNode);

        $connection = $this->createConnection($endpoint, $targetNode);
        $definition->setType($connection->getName());
        $definition->setList(false);
        $definition->setNode($target);
        $definition->setMeta('pagination', $config);

        if (!$definition->getResolver()) {
            $definition->setResolver(AllNodesWithPagination::class);
        }

        $this->filterFactory->build($definition, $targetNode, $endpoint);

        //deprecated, keep for BC with v1
        if ($this->bcConfig['filters'] ?? false) {
            $this->addFilters($definition, $target, $endpoint);
        }
    }

    /**
     * @param Endpoint            $endpoint
     * @param DefinitionInterface $node
     *
     * @return ObjectDefinition
     */
    private function createConnection(Endpoint $endpoint, DefinitionInterface $node): ObjectDefinition
    {
        $connection = new ObjectDefinition();
        $connection->setName("{$node->getName()}Connection");

        if (!$endpoint->hasType($connection->getName())) {
            $endpoint->addType($connection);

            $totalCount = new FieldDefinition();
            $totalCount->setName('totalCount');
            $totalCount->setType('Int');
            $totalCount->setNonNull(true);
            $connection->addField($totalCount);

            $pages = new FieldDefinition();
            $pages->setName('pages');
            $pages->setType('Int');
            $pages->setNonNull(true);
            $connection->addField($pages);

            $pageInfo = new FieldDefinition();
            $pageInfo->setName('pageInfo');
            $pageInfo->setType('PageInfo');
            $pageInfo->setNonNull(true);
            $connection->addField($pageInfo);

            $edgeObject = new ObjectDefinition();
            $edgeObject->setName("{$node->getName()}Edge");
            if (!$endpoint->hasType($edgeObject->getName())) {
                $endpoint->addType($edgeObject);

                $nodeField = new FieldDefinition();
                $nodeField->setName('node');
                $nodeField->setType($node->getName());
                $nodeField->setNonNull(true);
                $edgeObject->addField($nodeField);

                $cursor = new FieldDefinition();
                $cursor->setName('cursor');
                $cursor->setType('string');
                $cursor->setNonNull(true);
                $edgeObject->addField($cursor);
            }

            $edges = new FieldDefinition();
            $edges->setName('edges');
            $edges->setType($edgeObject->getName());
            $edges->setList(true);
            $connection->addField($edges);
        } else {
            $connection = $endpoint->getType($connection->getName());
        }

        return $connection;
    }

    /**
     * @param Endpoint                       $endpoint
     * @param ExecutableDefinitionInterface  $query
     * @param FieldsAwareDefinitionInterface $node
     */
    private function createOrderBy(Endpoint $endpoint, ExecutableDefinitionInterface $query, FieldsAwareDefinitionInterface $node)
    {
        /** @var InputObjectDefinition $orderBy */
        $orderBy = unserialize(serialize($endpoint->getType(OrderBy::class)), ['allowed_classes' => true]); //clone recursively
        $orderBy->setName("{$node->getName()}OrderBy");

        if (!$endpoint->hasType($orderBy->getName())) {
            $orderByFields = new EnumDefinition();
            $orderByFields->setName("{$node->getName()}OrderByField");
            $options = $query->getMeta('pagination')['order_by'] ?? ['*'];
            $options = FieldOptionsHelper::normalize($options);

            foreach ($node->getFields() as $field) {
                if (!FieldOptionsHelper::isEnabled($options, $field->getName())) {
                    continue;
                }

                //ignore if non related to entity property
                if ($field->getOriginType() !== \ReflectionProperty::class) {
                    continue;
                }

                //ignore if is a list
                if ($field->isList()) {
                    continue;
                }

                //ignore if is related to other object
                if ($endpoint->hasType($field->getType()) && $endpoint->getType($field->getType()) instanceof FieldsAwareDefinitionInterface) {
                    continue;
                }

                $definition = new EnumValueDefinition($field->getName());
                $definition->setMeta('resolver', OrderBySimpleField::class);
                $definition->setMeta('field', $field->getName());

                $orderByFields->addValue($definition);
            }

            //configure custom orderBy and support for children, like "parentName" => parent.name
            foreach ($options as $fieldName => $config) {
                if ('*' === $fieldName || '*' === $config || !FieldOptionsHelper::isEnabled($options, $fieldName)) {
                    continue;
                }

                if (array_key_exists($fieldName, $orderByFields->getValues())) {
                    continue;
                }

                $field = $fieldName;
                $resolver = OrderBySimpleField::class;
                if (strpos($config, '.') !== false) {
                    $resolver = OrderByRelatedField::class;
                    $field = $config;
                } elseif (is_string($config)) {
                    $field = $config;
                }

                if (class_exists($config) && is_a($config, OrderByInterface::class, true)) {
                    $resolver = $config;
                    $field = $fieldName;
                }

                $definition = new EnumValueDefinition($fieldName);
                $definition->setMeta('resolver', $resolver);
                $definition->setMeta('field', $field);

                $orderByFields->addValue($definition);
            }

            if ($orderByFields->getValues()) {
                $orderBy->getField('field')->setType($orderByFields->getName());
                $endpoint->addType($orderByFields);
                $endpoint->addType($orderBy);
            } else {
                return;
            }
        } else {
            $orderBy = $endpoint->getType($orderBy->getName());
        }

        $arg = new ArgumentDefinition();
        $arg->setName('order');
        $arg->setType($orderBy->getName());
        $arg->setNonNull(false);
        $arg->setList(true);
        $arg->setDescription('Ordering options for this list.');
        $query->addArgument($arg);

        //to keep BC
        if ($this->bcConfig['orderBy'] ?? false) {
            $arg = new ArgumentDefinition();
            $arg->setName('orderBy');
            $arg->setType(OrderBy::class);
            $arg->setNonNull(false);
            $arg->setList(true);
            $deprecateMessage = \is_string($this->bcConfig['orderBy']) ? $this->bcConfig['orderBy'] : '**DEPRECATED** use `order` instead.';
            $arg->setDescription($deprecateMessage);
            $query->addArgument($arg);
        }
    }

    /**
     * @param ExecutableDefinitionInterface $definition
     */
    private function addPaginationArguments(ExecutableDefinitionInterface $definition): void
    {
        $first = new ArgumentDefinition();
        $first->setName('first');
        $first->setType('int');
        $first->setNonNull(false);
        $first->setDescription('Returns the first *n* elements from the list.');
        $definition->addArgument($first);

        $last = new ArgumentDefinition();
        $last->setName('last');
        $last->setType('int');
        $last->setNonNull(false);
        $last->setDescription('Returns the last *n* elements from the list.');
        $definition->addArgument($last);

        $after = new ArgumentDefinition();
        $after->setName('after');
        $after->setType('string');
        $after->setNonNull(false);
        $after->setDescription('Returns the last *n* elements from the list.');
        $definition->addArgument($after);

        $before = new ArgumentDefinition();
        $before->setName('before');
        $before->setType('string');
        $before->setNonNull(false);
        $before->setDescription('Returns the last *n* elements from the list.');
        $definition->addArgument($before);

        $page = new ArgumentDefinition();
        $page->setName('page');
        $page->setType('integer');
        $page->setNonNull(false);
        $page->setDescription('Page to fetch in order to use page pagination instead of cursor based');
        $definition->addArgument($page);
    }

    /**
     * @param ExecutableDefinitionInterface $definition
     * @param string                        $targetType
     * @param Endpoint                      $endpoint
     *
     * @throws \ReflectionException
     *
     * @deprecated since v1.2, should use `where` instead
     */
    private function addFilters(ExecutableDefinitionInterface $definition, string $targetType, Endpoint $endpoint): void
    {
        $filterName = ucfirst($definition->getName()).'Filter';
        if ($endpoint->hasType($filterName)) {
            $filters = $endpoint->getType($filterName);
        } else {
            $filters = new InputObjectDefinition();
            $filters->setName($filterName);
            $endpoint->add($filters);

            $object = $endpoint->getType($targetType);
            if ($object instanceof FieldsAwareDefinitionInterface) {
                foreach ($object->getFields() as $field) {
                    if ('id' === $field->getName()
                        || !$field->getOriginName()
                        || \ReflectionProperty::class !== $field->getOriginType()) {
                        continue;
                    }

                    $filter = new FieldDefinition();
                    $filter->setName($field->getName());
                    $type = $field->getType();
                    if ($endpoint->hasType($type)) {
                        $typeDefinition = $endpoint->getType($type);
                        if (!$typeDefinition instanceof EnumDefinition) {
                            $type = 'ID';
                        }
                        $filter->setList(true);
                    }

                    // fields using custom object as type
                    // are not available for filters
                    if (TypeRegistry::getTypeMapp()) {
                        if (isset(TypeRegistry::getTypeMapp()[$type])) {
                            $class = TypeRegistry::getTypeMapp()[$type];
                            $ref = new \ReflectionClass($class);
                            if ($ref->isSubclassOf(ObjectType::class)) {
                                continue;
                            }
                        }
                    }

                    $filter->setType($type);
                    $filters->addField($filter);
                }
            }
        }

        if (!$filters->getFields()) {
            return;
        }

        $search = new ArgumentDefinition();
        $search->setName('filters');
        $search->setType($filters->getName());
        $deprecateMessage = \is_string($this->bcConfig['filters']) ? $this->bcConfig['filters'] : '**DEPRECATED** use `where` instead to filter the list.';
        $search->setDescription($deprecateMessage);
        $search->setNonNull(false);
        $definition->addArgument($search);
    }
}
