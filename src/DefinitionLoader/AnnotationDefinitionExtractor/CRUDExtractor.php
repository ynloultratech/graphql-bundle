<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DefinitionLoader\AnnotationDefinitionExtractor;

use Doctrine\Common\Inflector\Inflector;
use Ynlo\GraphQLBundle\Action\AddNode;
use Ynlo\GraphQLBundle\Action\RemoveNode;
use Ynlo\GraphQLBundle\Action\UpdateNode;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;
use Ynlo\GraphQLBundle\Model\AddNodePayload;
use Ynlo\GraphQLBundle\Model\RemoveNodePayload;
use Ynlo\GraphQLBundle\Model\UpdateNodePayload;
use Ynlo\GraphQLBundle\Query\Node\AllNodes;
use Ynlo\GraphQLBundle\Query\Node\Node;
use Ynlo\GraphQLBundle\Query\Node\Nodes;

/**
 * Extract predefined mutations for CRUD operations
 */
class CRUDExtractor extends AbstractAnnotationDefinitionExtractor
{
    private const DRY_RUN = [
        'name' => 'dryRun',
        'type' => 'boolean',
        'description' => 'Execute only a validation process without save anything.
Helpful to create a server side validation. 
Must check `constraintViolations` in the payload to get validation messages.',
    ];

    /**
     * {@inheritDoc}
     */
    public function supports($annotation): bool
    {
        return (
            $annotation instanceof Annotation\QueryGet
            || $annotation instanceof Annotation\QueryGetAll
            || $annotation instanceof Annotation\MutationDelete
            || $annotation instanceof Annotation\MutationAdd
            || $annotation instanceof Annotation\MutationUpdate
        );
    }

    /**
     * {@inheritDoc}
     */
    public function extract($annotation, \ReflectionClass $refClass, DefinitionManager $definitionManager)
    {
        $crudAnnotations[] = [];
        if ($annotation instanceof Annotation\QueryGet) {
            $crudAnnotations[] = $this->createGetNodeQuery($annotation, $refClass, $definitionManager);
            if ($annotation->pluralQuery) {
                $crudAnnotations[] = $this->createGetNodesQuery($annotation, $refClass, $definitionManager);
            }
        }

        if ($annotation instanceof Annotation\QueryGetAll) {
            $crudAnnotations[] = $this->createListNodeQuery($annotation, $refClass);
        }

        if ($annotation instanceof Annotation\MutationDelete) {
            $crudAnnotations[] = $this->createRemoveNodeMutation($annotation, $refClass);
        }

        if ($annotation instanceof Annotation\MutationAdd) {
            $crudAnnotations[] = $this->createAddNodeMutation($annotation, $refClass);
        }

        if ($annotation instanceof Annotation\MutationUpdate) {
            $crudAnnotations[] = $this->createUpdateNodeMutation($annotation, $refClass);
        }

        foreach ($crudAnnotations as $crudAnnotation) {
            $extractors = [
                new ActionExtractor(),
            ];

            foreach ($extractors as $extractor) {
                if ($extractor->supports($crudAnnotation)) {
                    $extractor->setReader($this->reader);
                    $extractor->extract($crudAnnotation, $refClass, $definitionManager);
                }
            }
        }
    }

    /**
     * @param Annotation\QueryGet $annotation
     * @param \ReflectionClass    $refClass
     * @param DefinitionManager   $definitionManager
     *
     * @return Annotation\Query
     */
    protected function createGetNodeQuery($annotation, \ReflectionClass $refClass, DefinitionManager $definitionManager)
    {
        $type = $annotation->node ?? $this->getDefaultClassType($refClass);
        $name = $annotation->name ?? $this->getCanonicalName($type);

        $definition = $definitionManager->getType($type);
        if (!$definition->hasField($annotation->fetchBy)) {
            throw new \RuntimeException(sprintf('The field "%s" does not exist in "%s"', $annotation->fetchBy, $type));
        }
        $field = $definition->getField($annotation->fetchBy);

        return new Annotation\Query(
            [
                'type' => $type,
                'name' => $name,
                'resolver' => $refClass->hasMethod('__invoke') ? $refClass->getName() : Node::class,
                'deprecationReason' => $annotation->deprecationReason,
                'args' => [
                    new Annotation\Arg(
                        [
                            'name' => $field->getName(),
                            'type' => $field->getType(),
                            'internalName' => 'id',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * @param Annotation\QueryGet $annotation
     * @param \ReflectionClass    $refClass
     * @param DefinitionManager   $definitionManager
     *
     * @return Annotation\Query
     */
    protected function createGetNodesQuery($annotation, \ReflectionClass $refClass, DefinitionManager $definitionManager): Annotation\Query
    {
        $type = $annotation->node ?? $this->getDefaultClassType($refClass);
        $name = $annotation->pluralQueryName ?? $this->getCanonicalName(Inflector::pluralize($type));

        $definition = $definitionManager->getType($type);
        if (!$definition->hasField($annotation->fetchBy)) {
            throw new \RuntimeException(sprintf('The field "%s" does not exist in "%s"', $annotation->fetchBy, $type));
        }
        $field = $definition->getField($annotation->fetchBy);

        return new Annotation\Query(
            [
                'type' => "[$type!]!",
                'name' => $name,
                'resolver' => $refClass->hasMethod('__invoke') ? $refClass->getName() : Nodes::class,
                'deprecationReason' => $annotation->deprecationReason,
                'args' => [
                    new Annotation\Arg(
                        [
                            'name' => Inflector::pluralize($field->getName()), //by convention, pluralize
                            'type' => "[{$field->getType()}!]!",
                            'internalName' => 'ids',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * @param Annotation\QueryGetAll $annotation
     * @param \ReflectionClass       $refClass
     *
     * @return Annotation\Query
     */
    protected function createListNodeQuery($annotation, \ReflectionClass $refClass): Annotation\Query
    {
        $type = $annotation->node ?? $this->getDefaultClassType($refClass);
        $name = $annotation->name ?? $this->getCanonicalName('all'.ucfirst(Inflector::pluralize($type)));

        return new Annotation\Query(
            [
                'type' => "[$type]",
                'name' => $name,
                'resolver' => $refClass->hasMethod('__invoke') ? $refClass->getName() : AllNodes::class,
                'deprecationReason' => $annotation->deprecationReason,
            ]
        );
    }

    /**
     * @param Annotation\MutationDelete $annotation
     * @param \ReflectionClass          $refClass
     *
     * @return Annotation\Mutation
     */
    protected function createRemoveNodeMutation($annotation, \ReflectionClass $refClass): Annotation\Mutation
    {
        $type = $annotation->node ?? $this->getDefaultClassType($refClass);
        $name = $annotation->mutationName ?? $this->getCanonicalName('Remove'.ucfirst($type));

        return new Annotation\Mutation(
            [
                'name' => $name,
                'resolver' => $refClass->hasMethod('__invoke') ? $refClass->getName() : RemoveNode::class,
                'argsToInput' => true,
                'deprecationReason' => $annotation->deprecationReason,
                'args' => [
                    new Annotation\Arg(
                        [
                            'name' => 'id',
                            'type' => 'ID!',
                            'description' => "Id of the $type to remove",
                            'internalName' => 'node',
                        ]
                    ),
                ],
                'returns' => RemoveNodePayload::TYPE,
            ]
        );
    }

    /**
     * @param Annotation\MutationAdd $annotation
     * @param \ReflectionClass       $refClass
     *
     * @return Annotation\Mutation
     */
    protected function createAddNodeMutation($annotation, \ReflectionClass $refClass): Annotation\Mutation
    {
        $type = $annotation->node ?? $this->getDefaultClassType($refClass);
        $name = $annotation->name ?? $this->getCanonicalName('Add'.ucfirst($type));

        return new Annotation\Mutation(
            [
                'name' => $name,
                'resolver' => $refClass->hasMethod('__invoke') ? $refClass->getName() : AddNode::class,
                'input' => $annotation->input ?? $type,
                'type' => $type,
                'validationGroups' => $annotation->validationGroups,
                'deprecationReason' => $annotation->deprecationReason,
                'argsToInput' => true,
                'args' => array_merge(
                    [
                        new Annotation\Arg(self::DRY_RUN),
                    ],
                    $annotation->args
                ),
                'returns' => AddNodePayload::TYPE,
            ]
        );
    }

    /**
     * @param Annotation\MutationUpdate $annotation
     * @param \ReflectionClass          $refClass
     *
     * @return Annotation\Mutation
     */
    protected function createUpdateNodeMutation($annotation, \ReflectionClass $refClass): Annotation\Mutation
    {
        $type = $annotation->node ?? $this->getDefaultClassType($refClass);
        $name = $annotation->mutationName ?? $this->getCanonicalName('Update'.ucfirst($type));

        return new Annotation\Mutation(
            [
                'name' => $name,
                'resolver' => $refClass->hasMethod('__invoke') ? $refClass->getName() : UpdateNode::class,
                'input' => $annotation->input ?? $type,
                'type' => $type,
                'validationGroups' => $annotation->validationGroups,
                'deprecationReason' => $annotation->deprecationReason,
                'argsToInput' => true,
                'args' => [
                    new Annotation\Arg(
                        [
                            'name' => 'id',
                            'type' => 'ID!',
                            'description' => "Id of the $type to update",
                        ]
                    ),
                    new Annotation\Arg(self::DRY_RUN),
                ],
                'returns' => UpdateNodePayload::TYPE,
            ]
        );
    }

    /**
     * @param \ReflectionClass $refClass
     *
     * @return string
     */
    private function getDefaultClassType(\ReflectionClass $refClass): ?string
    {
        /** @var Annotation\ObjectType $objectType */
        $objectType = $this->reader->getClassAnnotation($refClass, Annotation\ObjectType::class);
        if ($objectType && $objectType->name) {
            return $objectType->name;
        }

        preg_match('/\w+$/', $refClass->getName(), $matches);

        return $matches[0] ?? null;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function getCanonicalName($name): string
    {
        return lcfirst(str_replace(' ', '', ucwords(strtr($name, '_-', '  '))));
    }
}
