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
use Ynlo\GraphQLBundle\Definition\ClassAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ImplementorInterface;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\NodeAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\NodeInterface;

class EndpointsDefinitionPlugin extends AbstractDefinitionPlugin
{
    /**
     * @var array
     */
    private $endpointAlias = [];

    /**
     * @var string
     */
    private $endpointDefault;


    /**
     * EndpointsDefinitionPlugin constructor.
     *
     * @param array $endpointsConfig
     */
    public function __construct(array $endpointsConfig)
    {
        $this->endpointAlias = $endpointsConfig['alias'] ?? [];
        $this->endpointDefault = $endpointsConfig['default'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function buildConfig(ArrayNodeDefinition $root): void
    {
        $root
            ->info('List of endpoints for queries and mutations')
            ->scalarPrototype();
    }

    /**
     * {@inheritDoc}
     */
    public function normalizeConfig(DefinitionInterface $definition, $config): array
    {
        $endpoints = $config['endpoints'] ?? [];

        //allow set only one endpoint in a simple string
        if (\is_string($endpoints)) {
            $endpoints = [$endpoints];
        }

        return $endpoints;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(DefinitionInterface $definition, Endpoint $endpoint, array $config): void
    {
        if ($endpoint->getName() === DefinitionRegistry::DEFAULT_ENDPOINT) {
            return;
        }

        //ignore safe operations
        if (\in_array($definition->getName(), ['node', 'nodes'])) {
            return;
        }

        //apply default endpoint to all operations
        $endpoints = $this->normalizeConfig($definition, $definition->getMeta('endpoints'));
        if (!$endpoints && $this->endpointDefault) {
            if ($definition instanceof QueryDefinition
                || ($definition instanceof ClassAwareDefinitionInterface
                    && is_subclass_of($definition->getClass(), NodeInterface::class, true))
            ) {
                $definition->setMeta('endpoints', ['endpoints' => [$this->endpointDefault]]);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureEndpoint(Endpoint $endpoint): void
    {
        if ($endpoint->getName() === DefinitionRegistry::DEFAULT_ENDPOINT) {
            return;
        }

        $forbiddenTypes = $this->getForbiddenTypes($endpoint);
        $this->processForbiddenTypes($endpoint, $forbiddenTypes);
    }

    protected function processForbiddenTypes(Endpoint $endpoint, $forbiddenTypes)
    {
        foreach ($endpoint->allQueries() as $queries) {
            $this->secureOperations($endpoint, $queries, $forbiddenTypes);
        }

        foreach ($endpoint->allMutations() as $mutations) {
            $this->secureOperations($endpoint, $mutations, $forbiddenTypes);
        }

        foreach ($endpoint->allTypes() as $type) {
            //remove implementations of forbidden interfaces
            if ($type instanceof ImplementorInterface) {
                foreach ($type->getInterfaces() as $interface) {
                    if (in_array($interface, $forbiddenTypes)) {
                        $type->removeInterface($interface);
                    }
                }
            }
            //remove fields related to forbidden interfaces
            if ($type instanceof FieldsAwareDefinitionInterface) {
                if ($type->getFields()) {
                    foreach ($type->getFields() as $field) {
                        //remove forbidden field
                        if (!$this->isGranted($endpoint, $field)) {
                            $type->removeField($field->getName());
                        }

                        //remove field related to forbidden type
                        $fieldType = $endpoint->hasType($field->getType()) ? $endpoint->getType($field->getType()) : null;
                        $fieldNodeType = $endpoint->hasType($field->getMeta('node')) ? $endpoint->getType($field->getMeta('node')) : null;
                        if (($fieldType && in_array($fieldType->getName(), $forbiddenTypes))
                            || ($fieldNodeType && in_array($fieldNodeType->getName(), $forbiddenTypes))) {
                            $type->removeField($field->getName());
                        }

                        foreach ($field->getArguments() as $argument) {
                            //remove forbidden argument
                            if (!$this->isGranted($endpoint, $argument)) {
                                $field->removeArgument($argument->getName());
                            }

                            //remove argument related to forbidden type
                            $argumentType = $endpoint->hasType($argument->getType()) ? $endpoint->getType($argument->getType()) : null;
                            if ($argumentType && \in_array($argumentType->getName(), $forbiddenTypes, true)) {
                                $field->removeArgument($argument->getName());
                            }
                        }
                    }

                    //after delete fields related to forbidden objects,
                    //verify if the object has at least one field
                    //otherwise mark this type as forbidden
                    if (!$type->getFields()) {
                        $forbiddenTypes[] = $type->getName();
                        $this->processForbiddenTypes($endpoint, $forbiddenTypes);
                    }
                }
            }
        }

        /** @var InterfaceDefinition $type */
        foreach ($endpoint->allInterfaces() as $type) {
            if ($type->getImplementors()) {
                foreach ($type->getImplementors() as $implementor) {
                    if (in_array($implementor, $forbiddenTypes)) {
                        $type->removeImplementor($implementor);
                    }
                }

                //after delete forbidden implementors
                //verify if the interface has at least one implementor
                //otherwise mark this interface as forbidden
                if (!$type->getImplementors()) {
                    $forbiddenTypes[] = $type->getName();
                    $this->processForbiddenTypes($endpoint, $forbiddenTypes);
                }
            }
        }

        foreach ($forbiddenTypes as $type) {
            $endpoint->removeType($type);
        }
    }

    /**
     * Remove
     *
     * @param Endpoint                      $endpoint
     * @param ExecutableDefinitionInterface $executableDefinition
     * @param array|string[]                $forbiddenTypes
     */
    protected function secureOperations(Endpoint $endpoint, ExecutableDefinitionInterface $executableDefinition, $forbiddenTypes)
    {
        $type = $endpoint->hasType($executableDefinition->getType()) ? $endpoint->getType($executableDefinition->getType()) : null;

        $node = null;
        //resolve the related node using interface
        if ($executableDefinition instanceof NodeAwareDefinitionInterface) {
            $node = $endpoint->hasType($executableDefinition->getNode()) ? $endpoint->getType($executableDefinition->getNode()) : null;
        }

        //resolve related node using metadata
        if (!$node) {
            $node = $endpoint->hasType($executableDefinition->getMeta('node')) ? $endpoint->getType($executableDefinition->getMeta('node')) : null;
        }

        $granted = true;
        $endpoints = $this->normalizeConfig($executableDefinition, $executableDefinition->getMeta('endpoints', []));

        //if the operation has endpoints defined use that,
        //otherwise check by related type and node
        if ($endpoints) {
            $granted = $this->isGranted($endpoint, $executableDefinition);
        } elseif (($type && \in_array($type->getName(), $forbiddenTypes, true))
                  || ($node && \in_array($node->getName(), $forbiddenTypes, true))) {
            $granted = false;
        }

        if (!$granted) {
            if ($executableDefinition instanceof MutationDefinition) {
                $endpoint->removeMutation($executableDefinition->getName());
            } else {
                $endpoint->removeQuery($executableDefinition->getName());
            }
        } else {
            foreach ($executableDefinition->getArguments() as $argument) {
                //remove forbidden argument
                if (!$this->isGranted($endpoint, $argument)) {
                    $executableDefinition->removeArgument($argument->getName());
                }

                //remove argument related to forbidden type
                $argumentType = $endpoint->hasType($argument->getType()) ? $endpoint->getType($argument->getType()) : null;
                if ($argumentType && \in_array($argumentType->getName(), $forbiddenTypes, true)) {
                    $executableDefinition->removeArgument($argument->getName());
                }
            }
        }
    }

    protected function getForbiddenTypes(Endpoint $endpoint)
    {
        $forbiddenTypes = [];
        foreach ($endpoint->allTypes() as $type) {
            if (!$this->isGranted($endpoint, $type)) {
                $forbiddenTypes[] = $type->getName();
            }
        }

        return $forbiddenTypes;
    }

    protected function isGranted(Endpoint $endpoint, DefinitionInterface $definition)
    {
        $endpoints = $this->normalizeConfig($definition, $definition->getMeta('endpoints', []));
        if ($endpoints) {
            $endpoints = $this->endpointsAliasToRealNames($endpoints);
        }

        return empty($endpoints) || \in_array($endpoint->getName(), $endpoints);
    }

    /**
     * Given array of endpoints (containing alias) return the array of specific endpoints (without aliases)
     *
     * ["all"] => ["admin", "frontend"]
     *
     * @param array $endpoints
     *
     * @return array
     */
    protected function endpointsAliasToRealNames($endpoints)
    {
        foreach ($endpoints as $index => $endpointName) {
            foreach ($this->endpointAlias as $alias => $targets) {
                if ($alias === $endpointName) {
                    $targets = $this->endpointsAliasToRealNames($targets);
                    unset($endpoints[$index]);
                    $endpoints = array_merge($endpoints, $targets);
                }
            }
        }

        return $endpoints;
    }
}
