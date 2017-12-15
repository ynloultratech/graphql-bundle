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
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Category;
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

        $mutation = <<<'GraphQL'
mutation ($input: AddPostInput!){
    posts {
        add(input: $input){
            node {
                title
                body
                status
                categories {
                    name
                }
            }
            clientMutationId
        }
    }
}
GraphQL;

        self::send(
            $mutation,
            [
                'input' => [
                    'title' => $title = $faker->sentence(),
                    'body' => $body = $faker->paragraph,
                    'status' => PostStatusType::PUBLISH,
                    'categories' => [
                        self::encodeID('Category', 1),
                        self::encodeID('Category', 2),
                    ],
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ]
        );

        self::assertRepositoryContains(Post::class, ['title' => $title, 'body' => $body]);
        self::assertJsonPathEquals($title, 'data.posts.add.node.title');
        self::assertJsonPathEquals($body, 'data.posts.add.node.body');
        self::assertJsonPathEquals(PostStatusType::PUBLISH, 'data.posts.add.node.status');
        self::assertJsonPathEquals($clientMutationId, 'data.posts.add.clientMutationId');

        $category1 = self::getRepository(Category::class)->find(1);
        self::assertJsonPathEquals($category1->getName(), 'data.posts.add.node.categories[0].name');

        $category2 = self::getRepository(Category::class)->find(2);
        self::assertJsonPathEquals($category2->getName(), 'data.posts.add.node.categories[1].name');
    }

    /**
     * testListPostWithCategories
     */
    public function testListPostWithCategories()
    {
        /** @var Post[] $records */
        $records = self::getRepository(Post::class)->findBy([], ['title' => 'ASC'], 3);

        $query = <<<'GraphQL'
query {
    posts {
        posts(first: 5, orderBy: {field: "title", direction: ASC}){
            edges {
                node {
                    title
                    categories {
                        name
                    }
                }
            }
        }
    }
}
GraphQL;
        self::send($query);

        foreach ($records as $index => $post) {
            self::assertJsonPathEquals($post->getTitle(), "data.posts.posts.edges[$index].node.title");
            self::assertJsonPathEquals($post->getCategories()->first()->getName(), "data.posts.posts.edges[$index].node.categories[0].name");
        }
    }
}
