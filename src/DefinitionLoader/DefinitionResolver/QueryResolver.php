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

use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;
use Ynlo\GraphQLBundle\Type\TypeUtil;

/**
 * Resolve queries
 */
class QueryResolver implements DefinitionResolverInterface
{
    use AnnotationReaderAwareTrait;
    use ObjectQueryTrait;

    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\Query;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($annotation, \ReflectionClass $refClass, DefinitionManager $definitionManager)
    {
        /** @var Annotation\Query $annotation */
        $query = new QueryDefinition();

        if ($annotation->name) {
            $query->setName($annotation->name);
        } else {
            $query->setName($this->getDefaultName($refClass));
        }

        if ($definitionManager->hasQuery($query->getName())) {
            $query = $definitionManager->getQuery($query->getName());
        } else {
            $definitionManager->addQuery($query);
        }

        $objectDefinition = $this->getObjectDefinition($refClass, $definitionManager);
        if ($objectDefinition) {
            $query->setType($objectDefinition->getName());
            $query->setList($annotation->list);
        } else {
            $error = sprintf('Does not exist any valid type for class "%s"', $refClass->getName());
            throw new \RuntimeException($error);
        }

        $argAnnotations = $this->reader->getClassAnnotations($refClass);
        foreach ($argAnnotations as $argAnnotation) {
            if ($argAnnotation instanceof Annotation\Argument) {
                $arg = new ArgumentDefinition();
                $arg->setName($argAnnotation->name);
                $arg->setDescription($argAnnotation->description);
                $arg->setInternalName($argAnnotation->internalName);
                $arg->setDefaultValue($argAnnotation->defaultValue);
                $arg->setType(TypeUtil::normalize($argAnnotation->type));
                $arg->setList(TypeUtil::isTypeList($argAnnotation->type));
                $arg->setNonNullList(TypeUtil::isTypeNonNullList($argAnnotation->type));
                $arg->setNonNull(TypeUtil::isTypeNonNull($argAnnotation->type));
                $query->addArgument($arg);
            }
        }

        $query->setResolver($refClass->getName());
        $query->setDeprecationReason($annotation->deprecationReason);
        $query->setDescription($annotation->description);
    }
}
