<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DefinitionLoader\DefinitionResolver;

use Doctrine\Common\Util\Inflector;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;
use Ynlo\GraphQLBundle\Query\Node\Node;
use Ynlo\GraphQLBundle\Query\Node\Nodes;

/**
 * Resolve queries
 */
class QueryGetNodeResolver implements DefinitionResolverInterface
{
    use AnnotationReaderAwareTrait;
    use ObjectQueryTrait;

    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\QueryGet;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($annotation, \ReflectionClass $refClass, DefinitionManager $definitionManager)
    {
        /** @var Annotation\QueryGet $annotation */
        if ($annotation->name) {
            $name = $annotation->name;
        } else {
            $name = $this->getDefaultName($refClass);
        }
        $this->createGetNoneQuery($name, $annotation, $refClass, $definitionManager);

        if ($annotation->pluralQuery) {
            if ($annotation->pluralQueryName) {
                $name = $annotation->pluralQueryName;
            } else {
                $name = Inflector::pluralize($this->getDefaultName($refClass));
            }
            $this->createGetNoneQuery($name, $annotation, $refClass, $definitionManager, true);
        }
    }

    /**
     * @param string            $name
     * @param mixed             $annotation
     * @param \ReflectionClass  $refClass
     * @param DefinitionManager $definitionManager
     * @param bool              $plural
     */
    protected function createGetNoneQuery(
        $name,
        $annotation,
        \ReflectionClass $refClass,
        DefinitionManager $definitionManager,
        $plural = false
    ) {
        /** @var Annotation\QueryGet $annotation */
        $query = new QueryDefinition();
        $query->setName($name);

        $objectDefinition = null;
        /** @var Annotation\ObjectType $objectType */
        if ($objectType = $this->reader->getClassAnnotation($refClass, Annotation\ObjectType::class)) {
            $typeName = $definitionManager->getTypeForClass($refClass->getName());
            if ($typeName && $definitionManager->hasType($typeName)) {
                $objectDefinition = $definitionManager->getType($typeName);
            }
        }

        if (!$objectDefinition) {
            $error = sprintf('Does not exist any valid type for class "%s"', $refClass->getName());
            throw new \RuntimeException($error);
        }

        $fetchBy = $annotation->fetchBy ?? 'id';

        if (!$objectDefinition->hasField($fetchBy)) {
            $error = sprintf('The object type "%s" does not have a field "%s" to fetch record using this field.', $objectDefinition->getName(), $fetchBy);
            throw new \InvalidArgumentException($error);
        }

        $fieldDefinition = $objectDefinition->getField($fetchBy);
        $argument = new ArgumentDefinition();
        $argument->setName($plural ? Inflector::pluralize($fieldDefinition->getName()) : $fieldDefinition->getName());
        $argument->setType($fieldDefinition->getType());
        $argument->setList($plural);
        $argument->setDescription($fieldDefinition->getDescription());
        $argument->setInternalName($plural ? 'ids' : 'id');
        $query->addArgument($argument);

        $query->setType($objectDefinition->getName());
        $query->setList($plural);
        $query->setResolver($plural ? Nodes::class : Node::class);
        $query->setDeprecationReason($annotation->deprecationReason);
        $query->setDescription($annotation->description);

        $definitionManager->addQuery($query);
    }
}
