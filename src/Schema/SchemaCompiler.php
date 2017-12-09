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
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionManager;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Type\Types;

/**
 * GraphQL Schema compiler
 *
 * Compile all definitions into graphql-php schema
 */
class SchemaCompiler implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var DefinitionRegistry
     */
    protected $registry;

    /**
     * @var DefinitionManager
     */
    protected $manager;

    /**
     * SchemaCompiler constructor.
     *
     * @param DefinitionRegistry $registry
     */
    public function __construct(DefinitionRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param string $endpoint
     *
     * @return Schema
     */
    public function compile(string $endpoint = 'default'): Schema
    {
        $this->manager = $this->registry->getManager($endpoint);
        Types::setUp($this->container, $this->manager);

        //automatically create all interface implementors
        //to avoid empty interfaces
        foreach ($this->manager->allInterfaces() as $type) {
            foreach ($type->getImplementors() as $implementor) {
                if (!Types::has($implementor)) {
                    Types::create($implementor);
                }
            }
        }

        $config = [
            'types' => Types::all(),
            'typeLoader' => function ($name) {
                return Types::get($name);
            },
        ];

        if ($this->manager->allQueries()) {
            $config['query'] = Types::get('Query');
        }

        if ($this->manager->allMutations()) {
            $config['mutation'] = Types::get('Mutation');
        }

        return new Schema($config);
    }
}
