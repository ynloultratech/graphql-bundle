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
}
