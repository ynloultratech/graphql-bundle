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
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;
use Ynlo\GraphQLBundle\Query\Node\AllNodes;

/**
 * Resolve queries
 */
class QueryGetAllNodes implements DefinitionResolverInterface
{
    use AnnotationReaderAwareTrait;
    use ObjectQueryTrait;

    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\QueryGetAll;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($annotation, \ReflectionClass $refClass, DefinitionManager $definitionManager)
    {
        /** @var Annotation\QueryGetAll $annotation */
        if ($annotation->name) {
            $name = $annotation->name;
        } else {
            $name = 'all'.Inflector::pluralize(ucfirst($this->getDefaultName($refClass)));
        }

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

        $query->setType($objectDefinition->getName());
        $query->setList(true);
        $query->setResolver(AllNodes::class);
        $query->setDeprecationReason($annotation->deprecationReason);
        $query->setDescription($annotation->description);

        $definitionManager->addQuery($query);
    }
}
