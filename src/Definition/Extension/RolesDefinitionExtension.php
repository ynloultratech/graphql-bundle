<?php

/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Extension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

class RolesDefinitionExtension extends AbstractDefinitionExtension
{
    /**
     * @var bool[]
     */
    private $definitionVisited = [];

    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritDoc}
     */
    public function buildConfig(ArrayNodeDefinition $root): void
    {
        $root
            ->info('List of roles for queries and mutations')
            ->prototype('scalar')
            ->end();
    }

    /**
     * {@inheritDoc}
     */
    public function configureEndpoint(Endpoint $endpoint): void
    {
        $endpoint->setQueries($this->secureDefinitions($endpoint->allQueries(), $endpoint));
        $endpoint->setMutations($this->secureDefinitions($endpoint->allMutations(), $endpoint));
    }

    /**
     * @param ExecutableDefinitionInterface[]     $definitions
     * @param Endpoint                            $endpoint
     * @param FieldsAwareDefinitionInterface|null $parent
     *
     * @return ExecutableDefinitionInterface[]
     */
    private function secureDefinitions(array $definitions, Endpoint $endpoint, FieldsAwareDefinitionInterface $parent = null): array
    {
        $secureDefinitions = [];
        foreach ($definitions as $definition) {
            $key = spl_object_hash($definition);
            if (isset($this->definitionVisited[$key])) {
                continue;
            }
            $this->definitionVisited[$key] = true;

            $type = $endpoint->hasType($definition->getType()) ? $endpoint->getType($definition->getType()): null;

            if (($roles = $definition->getRoles()) && !$this->authorizationChecker->isGranted($roles)) {
                if ($parent) {
                    $parent->removeField($definition->getName());
                }

                continue;
            }

            $secureDefinitions[] = $definition;

            if ($type instanceof FieldsAwareDefinitionInterface && $fieldDefinitions = $type->getFields()) {
                $this->secureDefinitions($fieldDefinitions, $endpoint, $type);
            }
        }

        return $secureDefinitions;
    }
}
