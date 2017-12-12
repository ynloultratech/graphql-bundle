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
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\DeleteNodePayload;
use Ynlo\GraphQLBundle\Mutation\DeleteNodeMutation;

/**
 * Resolve queries
 */
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
        /** @var Annotation\MutationDelete $annotation */
        if (!$annotation->name) {
            $annotation->name = 'delete'.ucfirst($this->getDefaultName($refClass, $endpoint));
        }

        if ($endpoint->hasTypeForClass($refClass->getName())) {
            $annotation->formOptions = array_merge(['data_class' => $refClass->getName()], $annotation->formOptions);
        }

        parent::parse($annotation, $refClass, $endpoint);
    }

    /**
     * {@inheritdoc}
     */
    public function createMutation(Annotation\Mutation $annotation): MutationDefinition
    {
        $mutation = parent::createMutation($annotation);
        if (!$annotation->resolver) {
            $mutation->setResolver(DeleteNodeMutation::class);
        }

        if (!$annotation->payload) {
            $mutation->setType(DeleteNodePayload::class);
        }

        return $mutation;
    }
}
