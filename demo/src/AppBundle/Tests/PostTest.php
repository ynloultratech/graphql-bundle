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
use Ynlo\GraphQLBundle\Demo\AppBundle\Type\PostStatusType;
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
                    'status' => self::literalValue(PostStatusType::PUBLISH),
                    'authorId' => self::encodeID('User', 1),
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ],
            [
                'node' => [
                    '... on Post' => [
                        'title',
                        'body',
                        'status',
                    ],
                ],
                'clientMutationId',
            ]
        );

        self::assertRepositoryContains(Post::class, ['title' => $title, 'body' => $body]);
        self::assertJsonPathEquals($title, 'data.posts.add.node.title');
        self::assertJsonPathEquals($body, 'data.posts.add.node.body');
        self::assertJsonPathEquals(PostStatusType::PUBLISH, 'data.posts.add.node.status');
        self::assertJsonPathEquals($clientMutationId, 'data.posts.add.clientMutationId');
    }

    /**
     * testListPostWithCategories
     */
    public function testListPostWithCategories()
    {
        /** @var Post[] $records */
        $records = self::getRepository(Post::class)->findBy([], ['title' => 'ASC'], 3);
        self::query(
            'posts.all',
            ['first' => 5, 'orderBy' => ['field' => 'title', 'direction' => 'ASC']],
            [
                'edges' => [
                    'node' => [
                        'title',
                        'categories' => [
                            'name',
                        ],
                    ],
                ],
            ]
        );

        foreach ($records as $index => $post) {
            self::assertJsonPathEquals($post->getTitle(), "data.posts.all.edges[$index].node.title");
            self::assertJsonPathEquals($post->getCategories()->first()->getName(), "data.posts.all.edges[$index].node.categories[0].name");
        }
    }
}
