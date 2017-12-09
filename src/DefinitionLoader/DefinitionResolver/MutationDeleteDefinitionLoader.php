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
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;
use Ynlo\GraphQLBundle\Model\DeleteNodePayload;
use Ynlo\GraphQLBundle\Mutation\DeleteNodeMutation;

/**
 * Resolve queries
 */
class MutationDeleteDefinitionLoader extends MutationDefinitionLoader
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
    public function resolve($annotation, \ReflectionClass $refClass, DefinitionManager $definitionManager)
    {
        /** @var Annotation\MutationDelete $annotation */
        if (!$annotation->name) {
            $annotation->name = 'delete'.ucfirst($this->getDefaultName($refClass, $definitionManager));
        }

        if ($definitionManager->hasTypeForClass($refClass->getName())) {
            $annotation->formOptions = array_merge(['data_class' => $refClass->getName()], $annotation->formOptions);
        }

        parent::resolve($annotation, $refClass, $definitionManager);
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
