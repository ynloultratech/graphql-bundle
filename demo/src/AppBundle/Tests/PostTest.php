<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Tests;

use Faker\Factory;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Test\ApiTestCase;

/**
 * Class PostTest
 */
class PostTest extends ApiTestCase
{
    /**
     * testAddPost
     */
    public function testAddPost()
    {
        $faker = Factory::create();
        self::mutation(
            'posts.add',
            [
                'input' => [
                    'title' => $title = $faker->sentence(),
                    'body' => $body = $faker->paragraph,
                    'authorId' => self::encodeID('User', 1),
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ],
            [
                'node' => [
                    '... on Post' => [
                        'title',
                        'body',
                    ],
                ],
                'clientMutationId',
            ]
        );
        self::assertRepositoryContains(Post::class, ['title' => $title, 'body' => $body]);
        self::assertJsonPathEquals($title, 'data.posts.add.node.title');
        self::assertJsonPathEquals($body, 'data.posts.add.node.body');
        self::assertJsonPathEquals($clientMutationId, 'data.posts.add.clientMutationId');
    }
}
