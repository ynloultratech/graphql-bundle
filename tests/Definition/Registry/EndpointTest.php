<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Definition\Registry;

use PHPUnit\Framework\TestCase;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\PostComment;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\User;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model\HasAuthorInterface;

class EndpointTest extends TestCase
{
    public function testName()
    {
        $endpoint = new Endpoint('someName');
        self::assertEquals('someName', $endpoint->getName());
    }

    public function testTypes()
    {
        $endpoint = new Endpoint('endpoint');

        $user = new ObjectDefinition();
        $user->setName('User');
        $user->setClass(User::class);
        $endpoint->add($user);

        $admin = new ObjectDefinition();
        $admin->setName('Admin');
        $admin->setClass(User::class);
        $endpoint->add($admin);

        $hasAuthor = new InterfaceDefinition();
        $hasAuthor->setName('HasAuthor');
        $hasAuthor->setClass(HasAuthorInterface::class);
        $endpoint->add($hasAuthor);

        self::assertTrue($endpoint->hasType('User'));
        self::assertTrue($endpoint->hasType('Admin'));
        self::assertTrue($endpoint->hasType(User::class));
        self::assertTrue($endpoint->hasType('HasAuthor'));
        self::assertTrue($endpoint->hasTypeForClass(HasAuthorInterface::class));
        self::assertEquals('User', $endpoint->getTypeForClass(User::class));
        self::assertEquals(['User', 'Admin'], $endpoint->getTypesForClass(User::class));
        self::assertEquals(User::class, $endpoint->getClassForType('User'));
        self::assertNull($endpoint->getClassForType('PostComment'));
        self::assertEquals(['User' => $user, 'Admin' => $admin, 'HasAuthor' => $hasAuthor], $endpoint->allTypes());
        self::assertEquals(['HasAuthor' => $hasAuthor], $endpoint->allInterfaces());

        $comment = new ObjectDefinition();
        $comment->setName('PostComment');
        $comment->setClass(PostComment::class);

        $endpoint->setTypes([$comment]);
        self::assertTrue($endpoint->hasType('PostComment'));
        self::assertFalse($endpoint->hasType('User'));
        self::assertFalse($endpoint->hasType('HasAuthor'));
        self::assertEmpty($endpoint->allInterfaces());
        self::assertEquals($comment, $endpoint->getType('PostComment'));
        self::assertEquals($comment, $endpoint->getType(PostComment::class));

        $endpoint->removeType('PostComment');
        self::assertFalse($endpoint->hasType('PostComment'));

        $endpoint->setTypes([$user, $hasAuthor, $comment]);

        self::assertTrue($endpoint->hasType('PostComment'));
        self::assertTrue($endpoint->hasType('User'));
        self::assertTrue($endpoint->hasType('HasAuthor'));

        $endpoint->removeType('HasAuthor');

        self::assertTrue($endpoint->hasType('PostComment'));
        self::assertTrue($endpoint->hasType('User'));
        self::assertFalse($endpoint->hasType('HasAuthor'));

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Duplicate definition for type with name "User"');
        $endpoint->add($user);
    }

    public function testGetNonExitentType()
    {
        $endpoint = new Endpoint('endpoint');

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Does not exist a valid definition for "User" type');

        $endpoint->getType('User');
    }

    public function testGetNonExitentTypeByClass()
    {
        $endpoint = new Endpoint('endpoint');

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage(sprintf(sprintf('Does not exist any valid type for class "%s"', User::class)));

        $endpoint->getTypeForClass(User::class);
    }

    public function testQueries()
    {
        $endpoint = new Endpoint('endpoint');

        $allUsers = new QueryDefinition();
        $allUsers->setName('allUsers');
        $endpoint->add($allUsers);

        $allPosts = new QueryDefinition();
        $allPosts->setName('allPosts');
        $endpoint->add($allPosts);

        self::assertTrue($endpoint->hasQuery('allUsers'));
        self::assertTrue($endpoint->hasQuery('allPosts'));
        self::assertEquals(['allUsers' => $allUsers, 'allPosts' => $allPosts], $endpoint->allQueries());

        $endpoint->removeQuery('allUsers');

        self::assertFalse($endpoint->hasQuery('allUsers'));

        $endpoint->setQueries([$allUsers]);

        self::assertTrue($endpoint->hasQuery('allUsers'));
        self::assertFalse($endpoint->hasQuery('allPosts'));

        self::assertEquals($allUsers, $endpoint->getQuery('allUsers'));

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Duplicate definition for query with name "allUsers"');

        $endpoint->add($allUsers);
    }

    public function testMutations()
    {
        $endpoint = new Endpoint('endpoint');

        $addUser = new MutationDefinition();
        $addUser->setName('addUser');
        $endpoint->add($addUser);

        $addPost = new MutationDefinition();
        $addPost->setName('addPost');
        $endpoint->add($addPost);

        self::assertTrue($endpoint->hasMutation('addUser'));
        self::assertTrue($endpoint->hasMutation('addPost'));
        self::assertEquals(['addUser' => $addUser, 'addPost' => $addPost], $endpoint->allMutations());

        $endpoint->removeMutation('addUser');

        self::assertFalse($endpoint->hasMutation('addUser'));

        $endpoint->setMutations([$addUser]);

        self::assertTrue($endpoint->hasMutation('addUser'));
        self::assertFalse($endpoint->hasMutation('addPost'));

        self::assertEquals($addUser, $endpoint->getMutation('addUser'));

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Duplicate definition for query with name "addUser"');

        $endpoint->add($addUser);
    }
}
