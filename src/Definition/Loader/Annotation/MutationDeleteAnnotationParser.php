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
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Form\Node\NodeDeleteInput;
use Ynlo\GraphQLBundle\Model\DeleteNodePayload;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Mutation\DeleteNode;
use Ynlo\GraphQLBundle\Util\ClassUtils;

class MutationDeleteAnnotationParser extends MutationAnnotationParser
{
    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\MutationDelete;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        if (!$endpoint->hasTypeForClass($refClass->getName())) {
            throw new \RuntimeException(sprintf('Can\'t apply Delete operation to "%s", CRUD operations can only be applied to valid GraphQL object types.', $refClass->getName()));
        }

        if (!$refClass->implementsInterface(NodeInterface::class)) {
            throw new \RuntimeException(sprintf('Can\'t apply Delete operation to "%s", CRUD operations can only be applied to nodes. You are implementing NodeInterface in this class?', $refClass->getName()));
        }

        $definition = $endpoint->getType($endpoint->getTypeForClass($refClass->getName()));
        $bundleNamespace = ClassUtils::relatedBundleNamespace($refClass->getName());

        /** @var Annotation\MutationDelete $annotation */
        $annotation->name = $annotation->name ?? 'delete'.ucfirst($definition->getName());
        $annotation->payload = $annotation->payload ?? null;
        if (!$annotation->payload) {
            $annotation->payload = DeleteNodePayload::class;
        }
        $annotation->node = $annotation->node ?? $definition->getName();
        $annotation->options = array_merge(['form' => ['type' => NodeDeleteInput::class]], $annotation->options);
        $resolverReflection = new \ReflectionClass(DeleteNode::class);

        $resolver = ClassUtils::applyNamingConvention($bundleNamespace, 'Mutation', $definition->getName(), $annotation->name);
        if (class_exists($resolver)) {
            $annotation->resolver = $resolver;
        }

        parent::parse($annotation, $resolverReflection, $endpoint);
    }
}
