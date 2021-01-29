<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Loader\Annotation;

use Doctrine\Inflector\InflectorFactory;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Form\Node\NodeDeleteInput;
use Ynlo\GraphQLBundle\Model\AddNodePayload;
use Ynlo\GraphQLBundle\Model\DeleteNodePayload;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Model\UpdateNodePayload;
use Ynlo\GraphQLBundle\Mutation\AddNode;
use Ynlo\GraphQLBundle\Mutation\DeleteNode;
use Ynlo\GraphQLBundle\Mutation\UpdateNode;
use Ynlo\GraphQLBundle\Query\Node\AllNodesWithPagination;
use Ynlo\GraphQLBundle\Util\ClassUtils;

/**
 * CRUDAnnotationParser
 *
 * @deprecated in favor of QueryList, MutationAdd, MutationUpdate, MutationDelete parsers
 */
class CRUDAnnotationParser implements AnnotationParserInterface
{
    use AnnotationReaderAwareTrait;

    /**
     * @var QueryAnnotationParser
     */
    protected $queryParser;

    /**
     * @var MutationAnnotationParser
     */
    protected $mutationParser;

    /**
     * CRUDAnnotationParser constructor.
     *
     * @param QueryAnnotationParser    $queryParser
     * @param MutationAnnotationParser $mutationParser
     */
    public function __construct(QueryAnnotationParser $queryParser, MutationAnnotationParser $mutationParser)
    {
        $this->queryParser = $queryParser;
        $this->mutationParser = $mutationParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\CRUDOperations;
    }

    /**
     * {@inheritDoc}
     */
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        if (!$endpoint->hasTypeForClass($refClass->getName())) {
            throw new \RuntimeException(sprintf('Can`t apply CRUD operations to "%s", CRUD operations can only be applied to valid GraphQL object types.', $refClass->getName()));
        }

        if (!$refClass->implementsInterface(NodeInterface::class)) {
            throw new \RuntimeException(
                sprintf(
                    'Can`t apply CRUD operations to "%s", CRUD operations can only be applied to nodes.
             You are implementing NodeInterface in this class?',
                    $refClass->getName()
                )
            );
        }

        /** @var Annotation\CRUDOperations $annotation */
        if ($annotation->exclude) {
            $annotation->include = array_diff($annotation->include, $annotation->exclude);
        }

        $definition = $endpoint->getType($endpoint->getTypeForClass($refClass->getName()));

        $bundleNamespace = ClassUtils::relatedBundleNamespace($refClass->getName());

        //All query
        if (\in_array('list', $annotation->include, true)) {
            if ($annotation->list) {
                $query = $annotation->list;
            } else {
                $query = new Annotation\Query();
            }
            $this->createListOperation($definition, $query, $endpoint, $bundleNamespace);
        }

        //Add mutation
        if (\in_array('add', $annotation->include, true)) {
            if ($annotation->add) {
                $mutation = $annotation->add;
            } else {
                $mutation = new Annotation\Mutation();
            }
            $this->createAddOperation($definition, $mutation, $endpoint, $bundleNamespace);
        }

        //Update mutation
        if (\in_array('update', $annotation->include, true)) {
            if ($annotation->update) {
                $mutation = $annotation->update;
            } else {
                $mutation = new Annotation\Mutation();
            }
            $this->createUpdateOperation($definition, $mutation, $endpoint, $bundleNamespace);
        }

        //Delete mutation
        if (\in_array('delete', $annotation->include, true)) {
            if ($annotation->delete) {
                $mutation = $annotation->delete;
            } else {
                $mutation = new Annotation\Mutation();
            }
            $this->createDeleteOperation($definition, $mutation, $endpoint, $bundleNamespace);
        }
    }

    /**
     * @param ObjectDefinitionInterface $definition
     * @param Annotation\Query          $query
     * @param Endpoint                  $endpoint
     * @param string                    $bundleNamespace
     */
    protected function createListOperation(ObjectDefinitionInterface $definition, Annotation\Query $query, Endpoint $endpoint, $bundleNamespace)
    {
        $query->name = $query->name ?? 'all'.InflectorFactory::create()->build()->pluralize(ucfirst($definition->getName()));
        $query->type = $query->type ?? $definition->getName();
        $query->options = array_merge(['pagination' => true], $query->options);
        $resolver = ClassUtils::applyNamingConvention($bundleNamespace, 'Query', $definition->getName(), $query->name);
        if (class_exists($resolver)) {
            $query->resolver = $resolver;
        }

        $resolverReflection = new \ReflectionClass(AllNodesWithPagination::class);
        $this->queryParser->parse($query, $resolverReflection, $endpoint);
    }

    /**
     * @param ObjectDefinitionInterface $definition
     * @param Annotation\Mutation       $mutation
     * @param Endpoint                  $endpoint
     * @param string                    $bundleNamespace
     */
    protected function createAddOperation(ObjectDefinitionInterface $definition, Annotation\Mutation $mutation, Endpoint $endpoint, $bundleNamespace)
    {
        $mutation->name = $mutation->name ?? 'add'.ucfirst($definition->getName());
        $mutation->payload = $mutation->payload ?? null;
        if (!$mutation->payload) {
            //deep cloning
            /** @var ObjectDefinitionInterface $payload */
            $payload = unserialize(serialize($endpoint->getType(AddNodePayload::class)), [DefinitionInterface::class]);
            $payload->setName(ucfirst($mutation->name.'Payload'));

            if (!$endpoint->hasType($payload->getName())) {
                $payload->getField('node')->setType($definition->getName());
                $endpoint->add($payload);
            }

            $mutation->payload = $payload->getName();
        }
        $mutation->node = $mutation->node ?? $definition->getName();

        if ($endpoint->hasTypeForClass($mutation->node)) {
            $mutation->node = $endpoint->getTypeForClass($mutation->node);
        }

        $formType = true;
        $options = [];
        $generalForm = ClassUtils::applyNamingConvention($bundleNamespace, 'Form\Input', $mutation->node, $mutation->node, 'Input');
        $specificForm = ClassUtils::applyNamingConvention($bundleNamespace, 'Form\Input', $mutation->node, $mutation->name, 'Input');
        if (class_exists($specificForm)) {
            $formType = $specificForm;
        } elseif (class_exists($generalForm)) {
            $formType = $generalForm;
            $options['operation'] = $mutation->name;
        }

        $mutation->options = array_merge(['form' => ['type' => $formType, 'options' => $options]], $mutation->options);
        $resolverReflection = new \ReflectionClass(AddNode::class);

        $resolver = ClassUtils::applyNamingConvention($bundleNamespace, 'Mutation', $definition->getName(), $mutation->name);
        if (class_exists($resolver)) {
            $mutation->resolver = $resolver;
        }

        $this->mutationParser->parse($mutation, $resolverReflection, $endpoint);
    }

    /**
     * @param ObjectDefinitionInterface $definition
     * @param Annotation\Mutation       $mutation
     * @param Endpoint                  $endpoint
     * @param string                    $bundleNamespace
     */
    protected function createUpdateOperation(ObjectDefinitionInterface $definition, Annotation\Mutation $mutation, Endpoint $endpoint, $bundleNamespace)
    {
        $mutation->name = $mutation->name ?? 'update'.ucfirst($definition->getName());
        $mutation->payload = $mutation->payload ?? null;
        if (!$mutation->payload) {
            //deep cloning
            /** @var ObjectDefinitionInterface $payload */
            $payload = unserialize(serialize($endpoint->getType(UpdateNodePayload::class)), [DefinitionInterface::class]);
            $payload->setName(ucfirst($mutation->name.'Payload'));

            if (!$endpoint->hasType($payload->getName())) {
                $payload->getField('node')->setType($definition->getName());
                $endpoint->add($payload);
            }

            $mutation->payload = $payload->getName();
        }
        $mutation->node = $mutation->node ?? $definition->getName();

        if ($endpoint->hasTypeForClass($mutation->node)) {
            $mutation->node = $endpoint->getTypeForClass($mutation->node);
        }

        $formType = true;
        $options = [];
        $generalForm = ClassUtils::applyNamingConvention($bundleNamespace, 'Form\Input', $mutation->node, $mutation->node, 'Input');
        $specificForm = ClassUtils::applyNamingConvention($bundleNamespace, 'Form\Input', $mutation->node, $mutation->name, 'Input');
        if (class_exists($specificForm)) {
            $formType = $specificForm;
        } elseif (class_exists($generalForm)) {
            $formType = $generalForm;
            $options['operation'] = $mutation->name;
        }

        $mutation->options = array_merge(['form' => ['type' => $formType, 'options' => $options]], $mutation->options);
        $resolverReflection = new \ReflectionClass(UpdateNode::class);

        $resolver = ClassUtils::applyNamingConvention($bundleNamespace, 'Mutation', $definition->getName(), $mutation->name);
        if (class_exists($resolver)) {
            $mutation->resolver = $resolver;
        }

        $this->mutationParser->parse($mutation, $resolverReflection, $endpoint);
    }

    /**
     * @param ObjectDefinitionInterface $definition
     * @param Annotation\Mutation       $mutation
     * @param Endpoint                  $endpoint
     * @param string                    $bundleNamespace
     */
    protected function createDeleteOperation(ObjectDefinitionInterface $definition, Annotation\Mutation $mutation, Endpoint $endpoint, $bundleNamespace)
    {
        $mutation->name = $mutation->name ?? 'delete'.ucfirst($definition->getName());
        $mutation->payload = $mutation->payload ?? null;
        if (!$mutation->payload) {
            $mutation->payload = DeleteNodePayload::class;
        }
        $mutation->node = $mutation->node ?? $definition->getName();
        $mutation->options = array_merge(['form' => ['type' => NodeDeleteInput::class]], $mutation->options);
        $resolverReflection = new \ReflectionClass(DeleteNode::class);

        $resolver = ClassUtils::applyNamingConvention($bundleNamespace, 'Mutation', $definition->getName(), $mutation->name);
        if (class_exists($resolver)) {
            $mutation->resolver = $resolver;
        }

        $this->mutationParser->parse($mutation, $resolverReflection, $endpoint);
    }
}
