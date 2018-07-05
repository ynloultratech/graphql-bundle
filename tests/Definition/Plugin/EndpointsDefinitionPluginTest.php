<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Definition\Plugin;

use PHPUnit\Framework\TestCase;
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Plugin\EndpointsDefinitionPlugin;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\AddNodePayload;
use Ynlo\GraphQLBundle\Model\DeleteNodePayload;
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\Model\PageInfo;
use Ynlo\GraphQLBundle\Model\UpdateNodePayload;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\PostComment;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\User;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model\HasAuthorInterface;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class EndpointsDefinitionPluginTest extends TestCase
{
    public function testNormalizeConfig()
    {
        $plugin = new EndpointsDefinitionPlugin([]);
        $normalizedConfig = $plugin->normalizeConfig(new ObjectDefinition(), ['endpoints' => 'admin']);
        self::assertEquals(['admin'], $normalizedConfig);

        $normalizedConfig = $plugin->normalizeConfig(new ObjectDefinition(), ['endpoints' => ['frontend', 'admin']]);
        self::assertEquals(['frontend', 'admin'], $normalizedConfig);
    }

    public function testConfigure()
    {
        $endpoint = new Endpoint('admin');
        $plugin = new EndpointsDefinitionPlugin(['default' => 'admin']);

        $object = new ObjectDefinition();
        $object->setClass(User::class);
        $plugin->configure($object, $endpoint, []);
        self::assertEquals(['endpoints' => ['admin']], $object->getMeta('endpoints'));

        $query = new QueryDefinition();
        $plugin->configure($query, $endpoint, []);
        self::assertEquals(['endpoints' => ['admin']], $query->getMeta('endpoints'));

        $mutation = new MutationDefinition();
        $plugin->configure($mutation, $endpoint, []);
        self::assertEquals(['endpoints' => ['admin']], $mutation->getMeta('endpoints'));
    }

    public function testConfigureEndpoint()
    {
        $endpoint = new Endpoint('frontend');
        $plugin = new EndpointsDefinitionPlugin(
            [
                'default' => 'admin',
                'alias' => [
                    'all' => ['admin', 'frontend'],
                    'admin' => ['user_admin', 'system_admin'],
                ],
            ]
        );

        TestDefinitionHelper::loadAnnotationDefinitions(AddNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(UpdateNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(DeleteNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(PostComment::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(User::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(OrderBy::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(PageInfo::class, $endpoint);

        $customQuery = new QueryDefinition();
        $customQuery->setName('viewer');
        $customQuery->setMeta('node', User::class);
        $endpoint->add($customQuery);

        $endpoint->getType('User')->setMeta('endpoints', ['endpoints' => 'system_admin']);
        $endpoint->getType('Customer')->setMeta('endpoints', ['endpoints' => 'system_admin']);
        $endpoint->getType('Administrator')->setMeta('endpoints', ['endpoints' => 'system_admin']);
        $endpoint->getMutation('addPost')->setMeta('endpoints', ['endpoints' => 'admin']);
        $endpoint->getMutation('updatePost')->setMeta('endpoints', ['endpoints' => 'admin']);
        $endpoint->getMutation('deletePost')->setMeta('endpoints', ['endpoints' => 'admin']);


        /** @var ObjectDefinition $postDefinition */
        $postDefinition = $endpoint->getType('Post');
        $postDefinition->getField('hasTags')->setMeta('endpoints', ['endpoints' => 'admin']);

        self::assertTrue($endpoint->getType(Post::class)->hasField('hasTags'));
        self::assertTrue($endpoint->hasMutation('addUser'));
        self::assertTrue($endpoint->hasMutation('updateUser'));
        self::assertTrue($endpoint->hasMutation('deleteUser'));
        self::assertTrue($endpoint->hasQuery('viewer'));
        self::assertTrue($endpoint->hasType(HasAuthorInterface::class));
        self::assertTrue($endpoint->hasType('User'));
        self::assertTrue($endpoint->hasType('Customer'));
        self::assertTrue($endpoint->hasType('Administrator'));

        $plugin->configureEndpoint($endpoint);

        self::assertTrue($endpoint->hasQuery('allPosts'));
        self::assertFalse($endpoint->hasQuery('viewer'));
        self::assertFalse($endpoint->hasType('User'));
        self::assertFalse($endpoint->hasType('Customer'));
        self::assertFalse($endpoint->hasType('Administrator'));
        self::assertFalse($endpoint->hasMutation('addPost'));
        self::assertFalse($endpoint->hasMutation('updatePost'));
        self::assertFalse($endpoint->hasMutation('deletePost'));
        self::assertFalse($endpoint->hasMutation('addUser'));
        self::assertFalse($endpoint->hasMutation('updateUser'));
        self::assertFalse($endpoint->hasMutation('deleteUser'));
        self::assertFalse($endpoint->hasType(HasAuthorInterface::class));
        self::assertFalse($endpoint->getType(Post::class)->hasField('hasTags'));

        // print_r();
    }
}
