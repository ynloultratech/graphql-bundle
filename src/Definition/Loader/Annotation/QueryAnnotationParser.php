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

use Doctrine\Common\Annotations\Reader;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\ArgumentAwareInterface;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Util\ClassUtils;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * Parse Query annotation to fetch queries
 */
class QueryAnnotationParser implements AnnotationParserInterface
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * QueryAnnotationParser constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\Query;
    }

    /**
     * {@inheritdoc}
     *
     * @param Annotation\Query $annotation
     */
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        $query = new QueryDefinition();

        if ($annotation->name) {
            $query->setName($annotation->name);
        } else {
            $query->setName(lcfirst(ClassUtils::getDefaultName($refClass->getName())));
        }

        $endpoint->addQuery($query);

        if ($annotation->type) {
            $type = TypeUtil::normalize($annotation->type, $endpoint);
            if (class_exists($annotation->type)) {
                $type = $endpoint->getTypeForClass($annotation->type);
            }
            if ($endpoint->hasType($type)) {
                $query->setNode($type);
            }
            $query->setType($type);
        }

        $query->setList(TypeUtil::isTypeList($annotation->type));
        $query->setNonNull(TypeUtil::isTypeNonNull($annotation->type));
        $query->setNonNullList(TypeUtil::isTypeNonNullList($annotation->type));

        if (!$query->getType()) {
            $nodeType = ClassUtils::getNodeFromClass($refClass->getName());
            $objectDefinition = null;
            if ($nodeType && $endpoint->hasType($nodeType)) {
                $objectDefinition = $endpoint->getType($nodeType);
            }
            if ($objectDefinition) {
                $query->setType($objectDefinition->getName());
                $query->setNode($objectDefinition->getName());
            } else {
                //avoid throw Exception when the schema is empty
                //when the schema is empty the NodeInterface has not been loaded
                //then the node(id) and nodes(ids) queries fails
                if ('Node' === $nodeType) {
                    $endpoint->removeQuery($query->getName());

                    return;
                }

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

        foreach ($annotation->options as $option => $value) {
            $query->setMeta($option, $value);
        }
    }

    /**
     * @param ArgumentAwareInterface $argumentAware
     * @param Annotation\Argument    $argAnnotation
     */
    protected function resolveArgument(ArgumentAwareInterface $argumentAware, Annotation\Argument $argAnnotation): void
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
        foreach ($argAnnotation->options as $option => $value) {
            $arg->setMeta($option, $value);
        }
        $argumentAware->addArgument($arg);
    }
}
