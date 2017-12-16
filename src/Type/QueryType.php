<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Resolver\ResolverExecutor;
use Ynlo\GraphQLBundle\Type\Definition\EndpointAwareInterface;
use Ynlo\GraphQLBundle\Type\Definition\EndpointAwareTrait;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;
use Ynlo\GraphQLBundle\Util\GraphQLBuilder;

/**
 * Class QueryType
 */
class QueryType extends ObjectType implements
    ContainerAwareInterface,
    EndpointAwareInterface
{
    use ContainerAwareTrait;
    use EndpointAwareTrait;

    /**
     * QueryType constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $defaults = [
            'name' => 'Query',
            'fields' => function () {
                $queries = [];
                foreach ($this->endpoint->allQueries() as $query) {
                    $queries[$query->getName()] = $this->getQueryConfig($query);
                }

                return $queries;
            },
        ];
        parent::__construct(array_merge($defaults, $config));
    }

    /**
     * @param QueryDefinition $query
     *
     * @return array
     */
    protected function getQueryConfig(QueryDefinition $query): array
    {
        $config['type'] = TypeRegistry::get($query->getType());
        if ($query->isList()) {
            $config['type'] = Type::listOf($config['type']);
        }

        $config['args'] = GraphQLBuilder::buildArguments($query);

        $config['resolve'] = new ResolverExecutor($this->container, $this->endpoint, $query);
        $config['description'] = $query->getDescription();
        $config['deprecationReason'] = $query->getDeprecationReason();

        return $config;
    }
}
