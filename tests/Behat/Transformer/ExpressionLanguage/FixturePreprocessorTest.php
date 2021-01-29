<?php

namespace Ynlo\GraphQLBundle\Tests\Behat\Transformer\ExpressionLanguage;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Ynlo\GraphQLBundle\Behat\Fixtures\FixtureManager;
use Ynlo\GraphQLBundle\Behat\Transformer\ExpressionLanguage\FixturePreprocessor;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;


class FixturePreprocessorTest extends MockeryTestCase
{

    public function testSetUp()
    {
        $el = new ExpressionLanguage();
        $manager = \Mockery::mock(FixtureManager::class);

        $manager->expects('getFixtureGlobalId')->with('post1')->andReturn('Post:1');
        $manager->expects('getFixtureGlobalId')->with('post2')->andReturn('Post:2');
        $manager->expects('getFixtureGlobalId')->with('post3')->andReturn('Post:3');

        $post1 = new Post(1);
        $post2 = new Post(2);
        $post3 = new Post(3);
        $manager->expects('getFixture')->with('post1')->andReturn($post1);
        $manager->expects('getFixture')->with('post2')->andReturn($post2);
        $manager->expects('getFixture')->with('post3')->andReturn($post3);

        $processor = new FixturePreprocessor($manager);

        $expression = '{[#post1, #post2, #post3]}';
        $values = [];
        $processor->setUp($el, $expression, $values);

        self::assertEquals('{[post1_id, post2_id, post3_id]}', $expression);
        self::assertEquals(['post1_id' => 'Post:1', 'post2_id' => 'Post:2', 'post3_id' => 'Post:3'], $values);


        $expression = '{[@post1, @post2, @post3]}';
        $values = [];
        $processor->setUp($el, $expression, $values);

        self::assertEquals('{[post1, post2, post3]}', $expression);
        self::assertEquals(['post1' => $post1, 'post2' => $post2, 'post3' => $post3], $values);
    }
}
