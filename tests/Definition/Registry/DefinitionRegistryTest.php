<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Definition\Registry;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\Loader\DefinitionLoaderInterface;
use Ynlo\GraphQLBundle\Definition\Plugin\DefinitionPluginInterface;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Extension\EndpointNotValidException;
use Ynlo\GraphQLBundle\Model\AddNodePayload;
use Ynlo\GraphQLBundle\Model\DeleteNodePayload;
use Ynlo\GraphQLBundle\Model\UpdateNodePayload;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\User;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class DefinitionRegistryTest extends MockeryTestCase
{
    public function testGetEndpoint()
    {
        $loader = \Mockery::mock(DefinitionLoaderInterface::class);
        $loader->expects('loadDefinitions')->withArgs(
            function (Endpoint $endpoint) {

                TestDefinitionHelper::loadAnnotationDefinitions(AddNodePayload::class, $endpoint);
                TestDefinitionHelper::loadAnnotationDefinitions(UpdateNodePayload::class, $endpoint);
                TestDefinitionHelper::loadAnnotationDefinitions(DeleteNodePayload::class, $endpoint);
                TestDefinitionHelper::loadAnnotationDefinitions(User::class, $endpoint);

                $endpoint->getType('User')->setMeta('extraField', ['name' => 'FullName', 'type' => 'string']);

                TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint);

                return true;
            }
        );

        // create a fake plugin
        $plugin = \Mockery::mock(DefinitionPluginInterface::class);
        $plugin->allows('getName')->andReturn('extraField');
        $plugin->allows('buildConfig')->withArgs(
            function (ArrayNodeDefinition $root) {
                $config = $root
                    ->info('This plugin is useless, add extra fields to objects ;)')
                    ->children();

                $config->scalarNode('name');
                $config->scalarNode('type');

                return true;
            }
        );

        $plugin->allows('normalizeConfig')->withArgs(
            function (DefinitionInterface $definition, $config) {
                self::assertEquals('User', $definition->getName());
                self::assertEquals(
                    [
                        'name' => 'FullName',
                        'type' => 'string',
                    ],
                    $config
                );

                return true;
            }
        )->andReturnUsing(
            function (DefinitionInterface $definition, $config) {
                $config['name'] = lcfirst($config['name']);
                $config['type'] = ucfirst($config['type']);

                return $config;
            }
        );

        $plugin->allows('configure')->withArgs(
            function (DefinitionInterface $definition, Endpoint $endpoint, array $config) {
                if ($definition instanceof InterfaceDefinition && $definition->getName() === 'User') {
                    self::assertEquals('fullName', $config['name']);
                    self::assertEquals('String', $config['type']);

                    if (!$definition->hasField($config['name'])) {
                        $field = new FieldDefinition();
                        $field->setName($config['name']);
                        $field->setType($config['type']);
                        $definition->addField($field);
                    }
                }

                return true;
            }
        );

        $plugin->allows('configureEndpoint')->withArgs(
            function (Endpoint $endpoint) {

                /** @var InterfaceDefinition $userDefinition */
                $userDefinition = $endpoint->getType('User');
                self::assertTrue($userDefinition->hasField('fullName'));
                self::assertEquals('String', $userDefinition->getField('fullName')->getType());

                return true;
            }
        );

        $cache = \Mockery::mock(CacheInterface::class);

        $cache->expects('delete')->with('default.raw');
        $cache->expects('delete')->with('default');
        $cache->expects('delete')->with('admin');
        $cache->expects('delete')->with('frontend');

        $cache->allows('get')->withAnyArgs()->andReturnUsing(
            function ($endpoint, $callback) {
                return $callback();
            }
        );

        $registry = new DefinitionRegistry(
            $cache,
            [$loader],
            [$plugin],
            [
                'endpoints' => [
                    'admin' => [],
                    'frontend' => [],
                ],
            ]
        );

        $container = \Mockery::mock(ContainerInterface::class);
        $registry->setContainer($container);

        $registry->clearCache();
        $endpoint = $registry->getEndpoint('admin');

        /** @var InterfaceDefinition $userDefinition */
        $userDefinition = $endpoint->getType('User');
        self::assertTrue($userDefinition->hasField('fullName'));
        self::assertEquals('String', $userDefinition->getField('fullName')->getType());

        return $registry;
    }

    /**
     * @depends testGetEndpoint
     */
    public function testGetEndpointUsingStaticCache()
    {
        $config = [
            'endpoints' => [
                'admin' => [],
                'frontend' => [],
            ],
        ];

        $cache = \Mockery::mock(CacheInterface::class);

        /** @var DefinitionRegistry|Mock $registry */
        $registry = \Mockery::spy(DefinitionRegistry::class, [$cache, [], [], $config])
                            ->makePartial()
                            ->shouldAllowMockingProtectedMethods();

        $endpoint = $registry->getEndpoint('admin');

        $registry->shouldNotHaveReceived('initialize');

        /** @var InterfaceDefinition $userDefinition */
        $userDefinition = $endpoint->getType('User');
        self::assertTrue($userDefinition->hasField('fullName'));
        self::assertEquals('String', $userDefinition->getField('fullName')->getType());
    }

    public function testGetNotValidEndpoint()
    {
        self::expectException(EndpointNotValidException::class);
        $cache = \Mockery::mock(CacheInterface::class);
        $registry = new DefinitionRegistry($cache);
        $registry->getEndpoint('backend');
    }
}
