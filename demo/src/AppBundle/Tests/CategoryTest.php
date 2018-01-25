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

use Ynlo\GraphQLBundle\Demo\AppBundle\DBAL\Types\PostStatusType;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Category;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Test\ApiTestCase;

/**
 * Class CategoryTest
 */
class CategoryTest extends ApiTestCase
{
    /**
     * testListPostWithCategories
     */
    public function testListCategoryWithSomePosts()
    {
        /** @var Category[] $records */
        $records = self::getRepository(Category::class)->findBy([], ['name' => 'ASC'], 3);

        $query = <<<'GraphQL'
query {
    categories {
        all(first: 5, orderBy: {field: "name", direction: ASC}){
            edges {
                node {
                    name
                    posts (first: 2, orderBy: {field:"title", direction: ASC}) {
                        edges {
                            node {
                                title
                            }
                        }
                    }
                }
            }
        }
    }
}
GraphQL;
        self::send($query);

        foreach ($records as $index => $category) {
            self::assertResponseJsonValueEquals($category->getName(), "data.categories.all.edges[$index].node.name");
            /** @var Post[] $posts */
            $posts = self::getRepository(Post::class)
                         ->createQueryBuilder('o')
                         ->andWhere(':category MEMBER OF o.categories')
                         ->addOrderBy('o.title', 'ASC')
                         ->setMaxResults(2)
                         ->setParameter('category', $category)
                         ->getQuery()
                         ->getResult();

            foreach ($posts as $indexPost => $post) {
                self::assertResponseJsonValueEquals($post->getTitle(), "data.categories.all.edges[$index].node.posts.edges[$indexPost].node.title");
            }
        }
    }

    /**
     * testGetCategoryPostsByStatus
     */
    public function testGetCategoryPostsByStatus()
    {
        /** @var Category $category1 */
        $category1 = self::getFixtureReference('category1');

        /** @var Post $post */
        $publish1 = [];
        foreach ($category1->getPosts() as $post) {
            if ($post->getStatus() === PostStatusType::PUBLISH) {
                $publish1[] = ['status' => 'PUBLISHED'];
            }
        }

        $query = <<<'GraphQL'
query($id: ID!) {
    node (id: $id) {
       ... on Category {
            id
            name
            postsByStatus (first: 100, status: PUBLISHED){
                edges {
                    node {
                        status
                    }
                }
            }
       }
    }
}
GraphQL;
        self::send(
            $query,
            [
                'id' => self::encodeID($category1),
            ]
        );

        $resultCategory1 = self::getResponseJsonPathValue('data.node');

        self::assertEquals($category1->getName(), $resultCategory1['name']);

        $postsInCategory1 = self::getResponseJsonPathValue('data.node.postsByStatus.edges[*].node');

        self::assertEquals($publish1, $postsInCategory1);
    }

    /**
     * testGetCategoryPostsByStatus
     */
    public function testGetCategoryPostsByStatusVerifyMaxConcurrentUsage()
    {
        $query = <<<'GraphQL'
query($ids: [ID!]!) {
    nodes (ids: $ids) {
       ... on Category {
            id
            name
            postsByStatus (first: 100, status: PUBLISHED){
                edges {
                    node {
                        status
                    }
                }
            }
       }
    }
}
GraphQL;
        self::send(
            $query,
            [
                'ids' => [
                    self::getFixtureGlobalId('category1'),
                    self::getFixtureGlobalId('category2'),
                ],
            ]
        );

       self::assertResponseJsonValueEquals('The field "postsByStatus" can be fetched only once per query. This field can`t be used in a list.', 'errors[0].message');
    }
}
