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
use Ynlo\GraphQLBundle\Definition\QueryResolver;

/**
 * Class QueryType
 */
class QueryType extends ObjectType implements
    ContainerAwareInterface,
    DefinitionManagerAwareInterface
{
    use ContainerAwareTrait;
    use DefinitionManagerAwareTrait;

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
                foreach ($this->manager->allQueries() as $query) {
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
        $config['type'] = Types::get($query->getReturnType());
        if ($query->isReturnList()) {
            $config['type'] = Type::listOf($config['type']);
        }

        $config['args'] = $this->resolveArguments($query);

        $config['resolve'] = new QueryResolver($this->container, $this->manager, $query);
        $config['deprecationReason'] = $query->getDeprecationReason();

        return $config;
    }

    /**
     * @param QueryDefinition $query
     *
     * @return array
     */
    protected function resolveArguments(QueryDefinition $query): array
    {
        $args = [];
        foreach ($query->getArgs() as $argDefinition) {
            $arg = [];
            $arg['description'] = $argDefinition->getDescription();
            $type = Types::get($argDefinition->getType());

            if ($argDefinition->isList()) {
                if ($argDefinition->isNonNullList()) {
                    $type = Type::nonNull($type);
                }
                $type = Type::listOf($type);
            }

            if ($argDefinition->isNonNull()) {
                $type = Type::nonNull($type);
            }

            $arg['type'] = $type;
            if ($argDefinition->getDefaultValue()) {
                $arg['defaultValue'] = $argDefinition->getDefaultValue();
            }
            $args[$argDefinition->getName()] = $arg;
        }

        return $args;
    }
}
