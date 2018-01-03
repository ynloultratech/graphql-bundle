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
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Model\UpdateNodePayload;
use Ynlo\GraphQLBundle\Mutation\UpdateNode;
use Ynlo\GraphQLBundle\Util\ClassUtils;

class MutationUpdateAnnotationParser extends MutationAnnotationParser
{
    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\MutationUpdate;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        if (!$endpoint->hasTypeForClass($refClass->getName())) {
            throw new \RuntimeException(sprintf('Can\'t apply CRUD operations to "%s", CRUD operations can only be applied to valid GraphQL object types.', $refClass->getName()));
        }

        if (!$refClass->implementsInterface(NodeInterface::class)) {
            throw new \RuntimeException(sprintf('Can\'t apply CRUD operations to "%s", CRUD operations can only be applied to nodes. You are implementing NodeInterface in this class?', $refClass->getName()));
        }

        $definition = $endpoint->getType($endpoint->getTypeForClass($refClass->getName()));
        $bundleNamespace = ClassUtils::relatedBundleNamespace($refClass->getName());

        /** @var Annotation\MutationUpdate $annotation */
        $annotation->name = $annotation->name ?? 'update'.ucfirst($definition->getName());
        $annotation->payload = $annotation->payload ?? null;
        if (!$annotation->payload) {
            //deep cloning
            /** @var ObjectDefinitionInterface $payload */
            $payload = unserialize(serialize($endpoint->getType(UpdateNodePayload::class)), [DefinitionInterface::class]);
            $payload->setName(ucfirst($annotation->name.'Payload'));

            if (!$endpoint->hasType($payload->getName())) {
                $payload->getField('node')->setType($definition->getName());
                $endpoint->add($payload);
            }

            $annotation->payload = $payload->getName();
        }
        $annotation->node = $annotation->node ?? $definition->getName();

        if ($endpoint->hasTypeForClass($annotation->node)) {
            $annotation->node = $endpoint->getTypeForClass($annotation->node);
        }

        $formType = true;
        $options = [];
        $generalForm = ClassUtils::applyNamingConvention($bundleNamespace, 'Form\Input', $annotation->node, $annotation->node, 'Input');
        $specificForm = ClassUtils::applyNamingConvention($bundleNamespace, 'Form\Input', $annotation->node, $annotation->name, 'Input');
        if (class_exists($specificForm)) {
            $formType = $specificForm;
        } elseif (class_exists($generalForm)) {
            $formType = $generalForm;
            $options['operation'] = $annotation->name;
        }

        $annotation->options = array_merge(['form' => ['type' => $formType, 'options' => $options]], $annotation->options);
        $resolverReflection = new \ReflectionClass(UpdateNode::class);

        $resolver = ClassUtils::applyNamingConvention($bundleNamespace, 'Mutation', $definition->getName(), $annotation->name);
        if (class_exists($resolver)) {
            $annotation->resolver = $resolver;
        }

        parent::parse($annotation, $resolverReflection, $endpoint);
    }
}
