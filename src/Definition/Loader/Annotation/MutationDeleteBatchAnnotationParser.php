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
use Ynlo\GraphQLBundle\Form\Node\NodeDeleteBatchInput;
use Ynlo\GraphQLBundle\Model\DeleteBatchNodePayload;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Mutation\DeleteBatchNode;
use Ynlo\GraphQLBundle\Util\ClassUtils;

class MutationDeleteBatchAnnotationParser extends MutationAnnotationParser
{
    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\MutationDeleteBatch;
    }

    /**
     * {@inheritdoc}
     *
     * @param Annotation\MutationDelete $annotation
     */
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        if (!$endpoint->hasTypeForClass($refClass->getName())) {
            throw new \RuntimeException(sprintf('Can\'t apply DeleteBatch operation to "%s", CRUD operations can only be applied to valid GraphQL object types.', $refClass->getName()));
        }

        if (!$refClass->implementsInterface(NodeInterface::class)) {
            throw new \RuntimeException(sprintf('Can\'t apply DeleteBatch operation to "%s", CRUD operations can only be applied to nodes. You are implementing NodeInterface in this class?', $refClass->getName()));
        }

        $definition = $endpoint->getType($endpoint->getTypeForClass($refClass->getName()));
        $bundleNamespace = ClassUtils::relatedBundleNamespace($refClass->getName());

        $annotation->name = $annotation->name ?? 'deleteBatch'.ucfirst($definition->getName());
        $annotation->payload = $annotation->payload ?? null;
        if (!$annotation->payload) {
            $annotation->payload = DeleteBatchNodePayload::class;
        }
        $annotation->node = $annotation->node ?? $definition->getName();
        $annotation->options = array_merge(['form' => ['type' => NodeDeleteBatchInput::class]], $annotation->options);
        $resolverReflection = new \ReflectionClass(DeleteBatchNode::class);

        $resolver = ClassUtils::applyNamingConvention($bundleNamespace, 'Mutation', $definition->getName(), $annotation->name);
        if (class_exists($resolver)) {
            $annotation->resolver = $resolver;
        }

        parent::parse($annotation, $resolverReflection, $endpoint);
    }
}
