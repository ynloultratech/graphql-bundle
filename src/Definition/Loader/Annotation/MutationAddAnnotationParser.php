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
use Ynlo\GraphQLBundle\Model\AddNodePayload;
use Ynlo\GraphQLBundle\Mutation\AddNodeMutation;

/**
 * Resolve queries
 */
class MutationAddAnnotationParser extends MutationAnnotationParser
{
    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Annotation\MutationAdd;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        if (!$annotation->name) {
            $annotation->name = 'add'.ucfirst($this->getDefaultName($refClass, $endpoint));
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
            $mutation->setResolver(AddNodeMutation::class);
        }

        if (!$annotation->payload) {
            $mutation->setType(AddNodePayload::class);
        }

        return $mutation;
    }
}
