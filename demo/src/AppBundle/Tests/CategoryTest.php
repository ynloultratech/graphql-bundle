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

use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Category;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Demo\AppBundle\Type\PostStatusType;
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
        self::query(
            'categories.all',
            ['first' => 5, 'orderBy' => ['field' => 'name', 'direction' => 'ASC']],
            [
                'edges' => [
                    'node' => [
                        'name',
                        'posts' => [
                            ['first' => 2, 'orderBy' => ['field' => 'title', 'direction' => 'ASC']],
                            [
                                'edges' => [
                                    'node' => [
                                        'title',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        foreach ($records as $index => $category) {
            self::assertJsonPathEquals($category->getName(), "data.categories.all.edges[$index].node.name");
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
                self::assertJsonPathEquals($post->getTitle(), "data.categories.all.edges[$index].node.posts.edges[$indexPost].node.title");
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

        /** @var Category $category2 */
        $category2 = self::getFixtureReference('category2');

        /** @var Post $post */
        $publish1 = [];
        foreach ($category1->getPosts() as $post) {
            if ($post->getStatus() === PostStatusType::PUBLISH) {
                $publish1[] = ['status' => $post->getStatus()];
            }
        }

        /** @var Post $post */
        $publish2 = [];
        foreach ($category2->getPosts() as $post) {
            if ($post->getStatus() === PostStatusType::PUBLISH) {
                $publish2[] = ['status' => $post->getStatus()];
            }
        }

        self::query(
            'categories.categories',
            ['ids' => [self::encodeID('Category', $category1->getId()), self::encodeID('Category', $category2->getId())]],
            [
                'id',
                'name',
                'postsByStatus' => [
                    ['first' => 100, 'status' => self::literalValue(PostStatusType::PUBLISH)],
                    [
                        'edges' => [
                            'node' => [
                                'status',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $resultCategory1 = self::getJsonPathValue('data.categories.categories[0]');
        $resultCategory2 = self::getJsonPathValue('data.categories.categories[1]');

        self::assertEquals($category1->getName(), $resultCategory1['name']);
        self::assertEquals($category2->getName(), $resultCategory2['name']);

        $postsInCategory1 = self::getJsonPathValue('data.categories.categories[0].postsByStatus.edges[*].node');
        $postsInCategory2 = self::getJsonPathValue('data.categories.categories[1].postsByStatus.edges[*].node');

        self::assertEquals($publish1, $postsInCategory1);
        self::assertEquals($publish2, $postsInCategory2);
    }
}
