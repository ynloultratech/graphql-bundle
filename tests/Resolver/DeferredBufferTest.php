<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Resolver;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Ynlo\GraphQLBundle\Resolver\DeferredBuffer;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\User;

class DeferredBufferTest extends MockeryTestCase
{
    public function testBufer()
    {
        $user1 = new User(1);
        $user2 = new User(2);
        $post11 = new Post(11);
        $post22 = new Post(22);

        $userExpr = \Mockery::mock(Expr::class);
        $userExpr->expects('in')->with('o.id', [1, 2]);
        $userQb = \Mockery::mock();
        $userQb->expects('expr')->andReturn($userExpr);
        $userQb->expects('where')->andReturn($userQb);
        $userQb->expects('getQuery')->andReturn($userQb);
        $userQb->expects('getResult')->andReturn([$user1, $user2]);
        $userRepo = \Mockery::mock(EntityRepository::class);
        $userRepo->expects('createQueryBuilder')->andReturn($userQb);

        $postExpr = \Mockery::mock(Expr::class);
        $postExpr->expects('in')->with('o.id', [11, 22]);
        $postQb = \Mockery::mock();
        $postQb->expects('expr')->andReturn($postExpr);
        $postQb->expects('where')->andReturn($postQb);
        $postQb->expects('getQuery')->andReturn($postQb);
        $postQb->expects('getResult')->andReturn([$post11, $post22]);
        $postRepo = \Mockery::mock(EntityRepository::class);
        $postRepo->expects('createQueryBuilder')->andReturn($postQb);

        $registry = \Mockery::mock(Registry::class);
        $registry->expects('getRepository')->with(User::class)->andReturn($userRepo);
        $registry->expects('getRepository')->with(Post::class)->andReturn($postRepo);

        $buffer = new DeferredBuffer($registry);

        $buffer->add($user1);
        $buffer->add($post11);
        $buffer->add($user2);
        $buffer->add($post22);

        $buffer->loadBuffer();

        self::assertEquals($user1, $buffer->getLoadedEntity($user1));
        self::assertEquals($user2, $buffer->getLoadedEntity($user2));
        self::assertEquals($post11, $buffer->getLoadedEntity($post11));
        self::assertEquals($post22, $buffer->getLoadedEntity($post22));

        //fallback
        $user3 = new User(3);
        self::assertEquals($user3, $buffer->getLoadedEntity($user3));
    }
}
