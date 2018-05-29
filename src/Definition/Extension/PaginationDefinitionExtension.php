<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Extension;

use Doctrine\Common\Annotations\Reader;
use GraphQL\Type\Definition\ObjectType;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\EnumDefinition;
use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\Query\Node\AllNodesWithPagination;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;

/**
 * Convert a simple return of nodes into a paginated collection with edges
 */
class PaginationDefinitionExtension extends AbstractDefinitionExtension
{
    public const ONE_TO_MANY = 'ONE_TO_MANY';
    public const MANY_TO_MANY = 'MANY_TO_MANY';

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var int
     */
    protected $limit;

    public function __construct(Reader $reader, array $config = [])
    {
        $this->reader = $reader;
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

        $search = new ArgumentDefinition();
        $search->setName('search');
        $search->setType('string');
        $search->setNonNull(false);
        $search->setDescription('Search in current list by given string');
        $definition->addArgument($search);

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

        if (!$definition->hasArgument('orderBy')) {
            $orderBy = new ArgumentDefinition();
            $orderBy->setName('orderBy');
            $orderBy->setType(OrderBy::class);
            $orderBy->setNonNull(false);
            $orderBy->setList(true);
            $orderBy->setDescription('Ordering options for this list.');
            $definition->addArgument($orderBy);
        } else {
            //if exist move to the end
            $orderBy = $definition->getArgument('orderBy');
            $definition->removeArgument('orderBy');
            $definition->addArgument($orderBy);
        }

        $target = null;
        if ($definition instanceof FieldDefinition) {
            $target = $definition->getType();
        }

        $target = $config['target'] ?? $target;
        if ($endpoint->hasTypeForClass($target)) {
            $target = $endpoint->getTypeForClass($target);
        }

        $connection = new ObjectDefinition();
        $connection->setName(ucfirst($target).'Connection');

        if (!$endpoint->hasType($connection->getName())) {
            $endpoint->addType($connection);

            $totalCount = new FieldDefinition();
            $totalCount->setName('totalCount');
            $totalCount->setType('Int');
            $totalCount->setNonNull(true);
            $connection->addField($totalCount);

            $pageInfo = new FieldDefinition();
            $pageInfo->setName('pageInfo');
            $pageInfo->setType('PageInfo');
            $pageInfo->setNonNull(true);
            $connection->addField($pageInfo);

            $edgeObject = new ObjectDefinition();
            $edgeObject->setName(ucfirst($target).'Edge');
            if (!$endpoint->hasType($edgeObject->getName())) {
                $endpoint->addType($edgeObject);

                $node = new FieldDefinition();
                $node->setName('node');
                $node->setType($target);
                $node->setNonNull(true);
                $edgeObject->addField($node);

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
        }

        $definition->setType($connection->getName());
        $definition->setList(false);
        $definition->setMeta('node', $target);
        $definition->setMeta('pagination', $config);

        if (!$definition->getResolver()) {
            $definition->setResolver(AllNodesWithPagination::class);
        }

        //TODO: add some config to customize filter
        $this->addFilters($definition, $target, $endpoint);
    }

    public function addFilters(ExecutableDefinitionInterface $definition, string $targetType, Endpoint $endpoint)
    {
        $filters = new InputObjectDefinition();
        $filters->setName(ucfirst($definition->getName()).'Filter');
        if ($endpoint->hasType($filters->getName())) {
            return;
        }

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

        if (!$filters->getFields()) {
            return;
        }

        $search = new ArgumentDefinition();
        $search->setName('filters');
        $search->setType($filters->getName());
        $search->setDescription('Filter the list by given filters');
        $search->setNonNull(false);
        $definition->addArgument($search);
    }
}
