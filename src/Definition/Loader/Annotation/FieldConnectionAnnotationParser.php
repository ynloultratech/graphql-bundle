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
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Extension\ExtensionManager;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * Resolve field of types queries using naming conventions
 */
class FieldConnectionAnnotationParser implements AnnotationParserInterface
{
    use AnnotationReaderAwareTrait;

    /**
     * @var ExtensionManager
     */
    protected $extensionManager;

    /**
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
        return $annotation instanceof Annotation\Field;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        return;
        /** @var Annotation\Field $annotation */
        $field = new FieldDefinition();

        if (!$refClass->hasMethod('__invoke')) {
            throw new \LogicException(sprintf('The class %s should have "__invoke" method to resolve field valued', $refClass->getName()));
        }

        if ($annotation->name) {
            $field->setName($annotation->name);
        } else {
            preg_match('/\w+$/', $refClass->getName(), $matches);
            $field->setName(lcfirst($matches[0] ?? ''));
        }

        $objectType = null;
        preg_match('/(\w+)\\\\Field\\\\(\w+)$/', $refClass->getName(), $matches);
        if (!isset($matches[1]) || !$endpoint->hasType($matches[1])) {
            $error = sprintf('Can`t resolve a valid object type for field "%s"', $refClass->getName());
            throw new \RuntimeException($error);
        }

        $objectDefinition = $endpoint->getType($matches[1]);
        if ($objectDefinition->hasField($field->getName())) {
            $field = $objectDefinition->getField($field->getName());
        } else {
            $objectDefinition->addField($field);
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
                $field->addArgument($arg);
            }
        }

        $field->setDeprecationReason($annotation->deprecationReason ?? $field->getDeprecationReason());
        $field->setDescription($annotation->description ?? $field->getDescription());
        $field->setType(TypeUtil::normalize($annotation->type) ?? $field->getType());

        if ($annotation->type) {
            $field->setList(TypeUtil::isTypeList($annotation->type));
            $field->setNonNull(TypeUtil::isTypeNonNull($annotation->type));
            $field->setNonNullList(TypeUtil::isTypeNonNullList($annotation->type));
        }

        $field->setResolver($refClass->getName());

        foreach ($this->extensionManager->getExtensions() as $extension) {
            $extension->configureDefinition($field, $refClass, $endpoint);
        }
    }
}
