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

        $mutation = <<<'GraphQL'
mutation ($input: AddPostInput!){
    posts {
        add(input: $input){
            node {
                title
                body
                status
                tags
                categories {
                    name
                }
            }
            clientMutationId
        }
    }
}
GraphQL;

        $category1 = self::getFixtureReference('category1');
        $category2 = self::getFixtureReference('category2');

        self::send(
            $mutation,
            [
                'input' => [
                    'title' => $title = $faker->sentence(),
                    'body' => $body = $faker->paragraph,
                    'status' => 'PUBLISHED',
                    'tags' => $tags = $faker->words(3),
                    'categories' => [
                        self::encodeID($category1),
                        self::encodeID($category2),
                    ],
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ]
        );

        self::assertRepositoryContains(Post::class, ['title' => $title, 'body' => $body]);
        self::assertResponseJsonPathEquals($title, 'data.posts.add.node.title');
        self::assertResponseJsonPathEquals($body, 'data.posts.add.node.body');
        self::assertResponseJsonPathEquals('PUBLISHED', 'data.posts.add.node.status');
        self::assertResponseJsonPathEquals($clientMutationId, 'data.posts.add.clientMutationId');
        self::assertResponseJsonPathEquals($tags, 'data.posts.add.node.tags');
        self::assertResponseJsonPathEquals($category1->getName(), 'data.posts.add.node.categories[0].name');
        self::assertResponseJsonPathEquals($category2->getName(), 'data.posts.add.node.categories[1].name');
    }

    /**
     * testAddPost
     */
    public function testAddPostWithAFutureDate()
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
                futurePublishDate
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
                    'status' => 'FUTURE',
                    'futurePublishDate' => $date = '1985-06-18T18:05:00-05:00',
                    'categories' => [
                        self::getFixtureGlobalId('category1'),
                        self::getFixtureGlobalId('category2'),
                    ],
                    'clientMutationId' => (string) $clientMutationId = mt_rand(),
                ],
            ]
        );

        self::assertRepositoryContains(
            Post::class,
            [
                'title' => $title,
                'body' => $body,
                'futurePublishDate' => date_create_from_format(DATE_ATOM, $date),
            ]
        );
        self::assertResponseJsonPathEquals($title, 'data.posts.add.node.title');
        self::assertResponseJsonPathEquals($body, 'data.posts.add.node.body');
        self::assertResponseJsonPathEquals('FUTURE', 'data.posts.add.node.status');
        self::assertResponseJsonPathEquals($date, 'data.posts.add.node.futurePublishDate');
        self::assertResponseJsonPathEquals($clientMutationId, 'data.posts.add.clientMutationId');
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
        all(first: 5, orderBy: {field: "title", direction: ASC}){
            edges {
                node {
                    title
                    tags
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
            self::assertResponseJsonPathEquals($post->getTitle(), "data.posts.all.edges[$index].node.title");
            self::assertResponseJsonPathEquals($post->getCategories()->first()->getName(), "data.posts.all.edges[$index].node.categories[0].name");
            self::assertResponseJsonPathEquals($post->getTags(), "data.posts.all.edges[$index].node.tags");
        }
    }
}
