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
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Extension\ExtensionManager;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * Parse Query annotation to fetch queries
 */
class QueryAnnotationParser implements AnnotationParserInterface
{
    use AnnotationReaderAwareTrait;
    use AnnotationParserHelper;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * @var ExtensionManager
     */
    protected $extensionManager;

    /**
     * QueryGetAllNodesAnnotationParser constructor.
     *
     * @param ExtensionManager $extensionManager
     */
    public function __construct(ExtensionManager $extensionManager)
    {
        $this->extensionManager = $extensionManager;
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
     */
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;

        /** @var Annotation\Query $annotation */
        $query = new QueryDefinition();

        if ($annotation->name) {
            $query->setName($annotation->name);
        } else {
            $query->setName(lcfirst($this->getDefaultName($refClass, $endpoint)));
        }

        if ($endpoint->hasQuery($query->getName())) {
            $query = $endpoint->getQuery($query->getName());
        } else {
            $endpoint->addQuery($query);
        }

        $objectDefinition = $this->getObjectDefinition($refClass, $endpoint);
        if ($objectDefinition) {
            $query->setType($objectDefinition->getName());
            $query->setList($annotation->list);
            $query->setMeta('node', $objectDefinition->getName());
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

        foreach ($this->extensionManager->getExtensions() as $extension) {
            $extension->configureDefinition($query, $refClass, $endpoint);
        }

        if (!$endpoint->hasQuery($query->getName())) {
            $endpoint->addQuery($query);
        }
    }
}
