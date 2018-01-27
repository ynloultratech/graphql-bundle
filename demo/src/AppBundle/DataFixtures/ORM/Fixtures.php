<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Ynlo\GraphQLBundle\Demo\AppBundle\DBAL\Types\PostStatusType;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Category;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\PostComment;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\User;

/**
 * Class FixtureManager
 */
class Fixtures extends Fixture
{
    public const USER_ADMIN = 'admin';

    protected $faker;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->faker = Factory::create();
        $this->faker->seed(1);
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->createUsers($manager);
        $this->createCategories($manager);
        $this->createPosts($manager);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    protected function createUsers(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername(self::USER_ADMIN);
        $user->setType(User::TYPE_ADMIN);
        $user->getProfile()->setEmail('admin@example.com');
        $this->setReference($user->getUsername(), $user);
        $manager->persist($user);

        for ($i = 1; $i <= 10; $i ++) {
            $user = new User();
            $user->setUsername($this->faker->userName);
            $user->getProfile()->setFirstName($this->faker->firstName);
            $user->getProfile()->setLastName($this->faker->lastName);
            $user->getProfile()->setEmail($this->faker->email);
            $user->getProfile()->setPhone($this->faker->phoneNumber);
            $user->getProfile()->setTwitter('#'.$user->getUsername());
            $user->getProfile()->setFacebook(strtolower($user->getProfile()->getFirstName().'.'.$user->getProfile()->getLastName()));
            $user->getProfile()->getAddress()->setStreet($this->faker->streetAddress);
            $user->getProfile()->getAddress()->setCity($this->faker->city);
            $user->getProfile()->getAddress()->setState($this->faker->countryCode);
            $user->getProfile()->getAddress()->setZipCode($this->faker->randomNumber(5));
            $this->setReference("user$i", $user);
            $manager->persist($user);
        }
    }

    /**
     * @param ObjectManager $manager
     */
    protected function createCategories(ObjectManager $manager)
    {
        $categories = ['Gadgets', 'Software', 'Servers', 'Development', 'Internet'];
        foreach ($categories as $index => $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);
            $this->setReference("category".($index + 1), $category);
        }
    }

    /**
     * @param ObjectManager $manager
     */
    protected function createPosts(ObjectManager $manager)
    {
        for ($i = 1; $i <= 20; $i ++) {
            $post = new Post();
            $author = $this->getReference('user'.$this->faker->numberBetween(1, 10));
            $post->setTitle($this->faker->sentence($this->faker->numberBetween(3, 10)));
            $post->setBody($this->faker->paragraph($this->faker->numberBetween(3, 10)));
            $post->setAuthor($author);
            $post->setTags($this->faker->words($this->faker->numberBetween(0, 4)));

            $cat = $this->faker->numberBetween(1, 5);
            $post->getCategories()->add($this->getReference('category'.$cat));
            if ($cat > 2 && $cat < 4) {
                $post->getCategories()->add($this->getReference('category'.($cat + 1)));
            }

            $status = [PostStatusType::DRAFT, PostStatusType::PENDING, PostStatusType::PUBLISH];
            $post->setStatus($status[array_rand($status)]);

            $manager->persist($post);

            $maxComments = $this->faker->numberBetween(1, 5);
            for ($ic = 1; $ic <= $maxComments; $ic ++) {
                $comment = new PostComment();
                $comment->setCommentable($post);
                $comment->setAuthor($this->getReference('user'.$this->faker->numberBetween(1, 10)));
                $comment->setBody($this->faker->sentence($this->faker->numberBetween(3, 10)));
                $manager->persist($comment);
            }

            $this->setReference("post$i", $post);
        }
    }
}
