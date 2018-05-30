<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Schema;

use GraphQL\Type\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;

/**
 * GraphQL Schema compiler
 *
 * Compile all definitions into graphql-php schema
 */
class SchemaCompiler implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $registry;

    public function __construct(DefinitionRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function compile(): Schema
    {
        $endpoint = $this->registry->getEndpoint();
        TypeRegistry::setUp($this->container, $endpoint);

        //automatically create all interface implementors
        //to avoid empty interfaces
        foreach ($endpoint->allInterfaces() as $type) {
            foreach ($type->getImplementors() as $implementor) {
                if (!TypeRegistry::has($implementor)) {
                    TypeRegistry::create($implementor);
                }
            }
        }

        $config = [
            'types' => TypeRegistry::all(),
            'typeLoader' => function ($name) {
                return TypeRegistry::get($name);
            },
        ];

        if ($endpoint->allQueries()) {
            $config['query'] = TypeRegistry::get('Query');
        }

        if ($endpoint->allMutations()) {
            $config['mutation'] = TypeRegistry::get('Mutation');
        }

        if (isset($config['query']) || isset($config['mutation'])) {
            return new Schema($config);
        }

        throw new \RuntimeException('Your GraphQL schema is empty. Create your first object and query and try again');
    }
}
