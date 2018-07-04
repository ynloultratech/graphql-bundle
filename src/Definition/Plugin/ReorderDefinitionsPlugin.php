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

use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * Reorder queries & mutations by name based on related node
 */
class ReorderDefinitionsPlugin extends AbstractDefinitionPlugin
{
    /**
     * {@inheritDoc}
     */
    public function configureEndpoint(Endpoint $endpoint): void
    {
        $endpoint->setQueries($this->sortQueries($endpoint->allQueries()));
        $endpoint->setMutations($this->sortQueries($endpoint->allMutations()));
    }

    /**
     * @param QueryDefinition[]|MutationDefinition[] $queries
     */
    private function sortQueries($queries): array
    {
        $sortedQueries = [];
        foreach ($queries as $query) {
            $name = $query->getName();
            $node = $query->getType();
            if ($query->getNode()) {
                $node = $query->getNode();
            }
            $sortedQueries[$node.'_'.$name] = $query;
        }
        ksort($sortedQueries);

        return $sortedQueries;
    }
}
