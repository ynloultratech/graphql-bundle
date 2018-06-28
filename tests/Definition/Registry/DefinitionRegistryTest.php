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

        $registry = new DefinitionRegistry(
            [$loader],
            [$plugin],
            null,
            [
                'endpoints' => [
                    'admin' => [],
                    'frontend' => [],
                ],
            ]
        );
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
        /** @var DefinitionRegistry|Mock $registry */
        $registry = \Mockery::spy(DefinitionRegistry::class, [[], [], null, $config])
                            ->makePartial()
                            ->shouldAllowMockingProtectedMethods();

        $endpoint = $registry->getEndpoint('admin');

        $registry->shouldNotHaveReceived('initialize');
        $registry->shouldNotHaveReceived('loadCache');

        /** @var InterfaceDefinition $userDefinition */
        $userDefinition = $endpoint->getType('User');
        self::assertTrue($userDefinition->hasField('fullName'));
        self::assertEquals('String', $userDefinition->getField('fullName')->getType());
    }

    /**
     * @depends testGetEndpoint
     */
    public function testGetEndpointUsingFileCache()
    {
        $config = [
            'endpoints' => [
                'admin' => [],
                'frontend' => [],
            ],
        ];
        /** @var DefinitionRegistry|Mock $registry */
        $registry = \Mockery::spy(DefinitionRegistry::class, [[], [], null, $config])
                            ->makePartial()
                            ->shouldAllowMockingProtectedMethods();

        //clear static cache to force load from file cache
        $ref = new \ReflectionClass(DefinitionRegistry::class);
        $refProp = $ref->getProperty('endpoints');
        $refProp->setAccessible(true);
        $refProp->setValue($registry, []);

        $endpoint = $registry->getEndpoint('admin');

        $registry->shouldNotHaveReceived('initialize');
        $registry->shouldHaveReceived('loadCache');

        /** @var InterfaceDefinition $userDefinition */
        $userDefinition = $endpoint->getType('User');
        self::assertTrue($userDefinition->hasField('fullName'));
        self::assertEquals('String', $userDefinition->getField('fullName')->getType());
    }

    public function testGetNotValidEndpoint()
    {

        self::expectException(EndpointNotValidException::class);
        $registry = new DefinitionRegistry();
        $registry->getEndpoint('backend');
    }
}
