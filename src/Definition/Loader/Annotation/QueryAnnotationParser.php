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

use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\ArgumentAwareInterface;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * Parse Query annotation to fetch queries
 */
class QueryAnnotationParser implements AnnotationParserInterface
{
    use AnnotationReaderAwareTrait;
    use AnnotationParserHelper;

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
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        /** @var Annotation\Query $annotation */
        $query = new QueryDefinition();

        if ($annotation->name) {
            $query->setName($annotation->name);
        } else {
            $query->setName(lcfirst($this->getDefaultName($refClass, $endpoint)));
        }

        $endpoint->addQuery($query);

        if ($annotation->node) {
            $query->setNode($annotation->node);
            $query->setType($annotation->node);
        }

        $query->setList($annotation->list);

        if (!$annotation->node) {
            $objectDefinition = $this->getObjectDefinition($refClass, $endpoint);
            if ($objectDefinition) {
                $query->setType($objectDefinition->getName());
                $query->setNode($objectDefinition->getName());
            } else {
                $error = sprintf('Does not exist any valid type for class "%s"', $refClass->getName());
                throw new \RuntimeException($error);
            }
        }

        if ($annotation->arguments) {
            foreach ($annotation->arguments as $argAnnotation) {
                if ($argAnnotation instanceof Annotation\Argument) {
                    $this->resolveArgument($query, $argAnnotation);
                }
            }
        } else {
            $argAnnotations = $this->reader->getClassAnnotations($refClass);
            foreach ($argAnnotations as $argAnnotation) {
                if ($argAnnotation instanceof Annotation\Argument) {
                    $this->resolveArgument($query, $argAnnotation);
                }
            }
        }

        $query->setResolver($annotation->resolver ?? $refClass->getName());
        $query->setDeprecationReason($annotation->deprecationReason);
        $query->setDescription($annotation->description);

        if (!$endpoint->hasQuery($query->getName())) {
            $endpoint->addQuery($query);
        }

        foreach ($annotation->options as $option => $value) {
            $query->setMeta($option, $value);
        }
    }

    /**
     * @param ArgumentAwareInterface $argumentAware
     * @param object                 $argAnnotation
     */
    public function resolveArgument(ArgumentAwareInterface $argumentAware, $argAnnotation)
    {
        $arg = new ArgumentDefinition();
        $arg->setName($argAnnotation->name);
        $arg->setDescription($argAnnotation->description);
        $arg->setInternalName($argAnnotation->internalName);
        $arg->setDefaultValue($argAnnotation->defaultValue);
        $arg->setType(TypeUtil::normalize($argAnnotation->type));
        $arg->setList(TypeUtil::isTypeList($argAnnotation->type));
        $arg->setNonNullList(TypeUtil::isTypeNonNullList($argAnnotation->type));
        $arg->setNonNull(TypeUtil::isTypeNonNull($argAnnotation->type));
        $argumentAware->addArgument($arg);
    }
}
