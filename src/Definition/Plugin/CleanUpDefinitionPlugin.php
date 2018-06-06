<?php

/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Plugin;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ImplementorInterface;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * This plugin remove non used definitions
 */
class CleanUpDefinitionPlugin extends AbstractDefinitionPlugin
{
    /**
     * @var Endpoint
     */
    protected $endpoint;

    protected $used = [];

    /**
     * {@inheritDoc}
     */
    public function buildConfig(ArrayNodeDefinition $root): void
    {
        $root
            ->info('Remove non used definitions')
            ->scalarPrototype();
    }

    /**
     * {@inheritDoc}
     */
    public function configureEndpoint(Endpoint $endpoint): void
    {
        if ($endpoint->getName() === DefinitionRegistry::DEFAULT_ENDPOINT) {
            return;
        }

        if ($endpoint->getName() !== 'pos') {
            return;
        }

        $this->endpoint = $endpoint;

        $this->processOperations($endpoint->allQueries());
        $this->processOperations($endpoint->allMutations());

        foreach ($endpoint->allTypes() as $type) {
            if (!\in_array($type->getName(), $this->used)) {
                $endpoint->removeType($type->getName());
            }
        }
    }

    /**
     * @param QueryDefinition[] $operations
     */
    protected function processOperations($operations)
    {
        foreach ($operations as $operation) {
            $this->used($operation->getType());
            foreach ($operation->getArguments() as $argument) {
                $this->used($argument->getType());
            }
        }
    }

    /**
     * @param string|DefinitionInterface $definition
     */
    protected function used($definition): void
    {
        if (\is_string($definition)) {
            if (!$this->endpoint->hasType($definition)) {
                return;
            }

            $definition = $this->endpoint->getType($definition);
        }

        if (\in_array($definition->getName(), $this->used)) {
            return;
        }

        $this->used[] = $definition->getName();
        if ($definition instanceof FieldsAwareDefinitionInterface) {
            foreach ($definition->getFields() as $field) {
                $this->used($field->getType());
                foreach ($field->getArguments() as $argument) {
                    $this->used($argument->getType());
                }
            }
        }

        if ($definition instanceof InterfaceDefinition) {
            foreach ($definition->getImplementors() as $implementor) {
                $this->used($implementor);
            }
        }

        if ($definition instanceof ImplementorInterface) {
            foreach ($definition->getInterfaces() as $interface) {
                $this->used($interface);
            }
        }
    }
}
