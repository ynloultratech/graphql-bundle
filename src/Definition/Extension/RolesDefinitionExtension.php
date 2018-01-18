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
     * @param ExecutableDefinitionInterface[] $definitions
     * @param Endpoint                        $endpoint
     *
     * @return ExecutableDefinitionInterface[]
     */
    private function secureDefinitions(array $definitions, Endpoint $endpoint): array
    {
        $secureDefinitions = [];
        foreach ($definitions as $definition) {
            if (($roles = $definition->getRoles()) && !$this->authorizationChecker->isGranted($roles)) {
                continue;
            }

            $secureDefinitions[] = $definition;

            /** @var FieldsAwareDefinitionInterface $type */
            $type = $endpoint->getType($definition->getType());
            if ($fields = $type->getFields()) {
                foreach ($fields as $fieldDefinition) {
                    if (($roles = $fieldDefinition->getRoles()) && !$this->authorizationChecker->isGranted($roles)) {
                        $type->removeField($fieldDefinition->getName());
                    }
                }
            }
        }

        return $secureDefinitions;
    }
}
