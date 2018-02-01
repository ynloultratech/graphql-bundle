<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

use Ynlo\GraphQLBundle\Behat\Assert\AssertJson;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Category;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Post;

/**
 * @method \Doctrine\Bundle\DoctrineBundle\Registry getDoctrine()
 */
trait CategoryContextTrait
{
    /**
     * @Given /^I can see ([^"]*) categories with no more than ([^"]*) posts ordered by "([^"]*)"$/
     */
    public function iCanSeeCategoriesWithNoMoreThanPostsOrderedBy($arg1, $arg2, $arg3)
    {
        $records = $this->getDoctrine()
                        ->getRepository(Category::class)
                        ->findBy([], ['name' => 'ASC'], $arg1);

        foreach ($records as $index => $category) {
            AssertJson::assertValueEquals($this->getRepose(), $category->getName(), "data.categories.all.edges[$index].node.name");

            /** @var Post[] $posts */
            $posts = $this->getDoctrine()
                          ->getRepository(Post::class)
                          ->createQueryBuilder('o')
                          ->andWhere(':category MEMBER OF o.categories')
                          ->addOrderBy('o.'.$arg3, 'ASC')
                          ->setMaxResults($arg2)
                          ->setParameter('category', $category)
                          ->getQuery()
                          ->getResult();

            foreach ($posts as $indexPost => $post) {
                AssertJson::assertValueEquals($this->getRepose(), $post->getTitle(), "data.categories.all.edges[$index].node.posts.edges[$indexPost].node.title");
            }
        }
    }
}