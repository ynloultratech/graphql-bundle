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
use Ynlo\GraphQLBundle\Definition\Plugin\CleanUpDefinitionPlugin;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\AddNodePayload;
use Ynlo\GraphQLBundle\Model\DeleteNodePayload;
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\Model\PageInfo;
use Ynlo\GraphQLBundle\Model\UpdateNodePayload;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Comment;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\User;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class CleanUpDefinitionPluginTest extends TestCase
{
    public function testConfigureEndpoint()
    {
        $plugin = new CleanUpDefinitionPlugin();
        $endpoint = new Endpoint('admin');

        TestDefinitionHelper::loadAnnotationDefinitions(AddNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(UpdateNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(DeleteNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(Comment::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(User::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(OrderBy::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(PageInfo::class, $endpoint);

        self::assertCount(17, $endpoint->allTypes());
        self::assertCount(4, $endpoint->allInterfaces());
        self::assertCount(8, $endpoint->allMutations());
        self::assertCount(2, $endpoint->allQueries());

        $plugin->configureEndpoint($endpoint);

        self::assertCount(13, $endpoint->allTypes());
        self::assertCount(4, $endpoint->allInterfaces());
        self::assertCount(8, $endpoint->allMutations());
        self::assertCount(2, $endpoint->allQueries());
    }

    public function testConfigureEndpointDefault()
    {
        $plugin = new CleanUpDefinitionPlugin();
        $endpoint = new Endpoint('default');

        TestDefinitionHelper::loadAnnotationDefinitions(AddNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(UpdateNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(DeleteNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(Comment::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(User::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(OrderBy::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(PageInfo::class, $endpoint);

        self::assertCount(17, $endpoint->allTypes());
        self::assertCount(4, $endpoint->allInterfaces());
        self::assertCount(8, $endpoint->allMutations());
        self::assertCount(2, $endpoint->allQueries());

        $plugin->configureEndpoint($endpoint);

        self::assertCount(17, $endpoint->allTypes());
        self::assertCount(4, $endpoint->allInterfaces());
        self::assertCount(8, $endpoint->allMutations());
        self::assertCount(2, $endpoint->allQueries());
    }
}
