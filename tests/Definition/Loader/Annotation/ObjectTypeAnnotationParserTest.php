<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Definition\Loader\Annotation;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Ynlo\GraphQLBundle\Annotation\InputObjectType;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\ObjectTypeAnnotationParser;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\User;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model\HasAuthorInterface;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model\Message;
use Ynlo\GraphQLBundle\Tests\TestAnnotationReader;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;
use Ynlo\GraphQLBundle\Type\OrderDirectionType;

class ObjectTypeAnnotationParserTest extends MockeryTestCase
{
    public function testSupports()
    {
        self::assertTrue((new ObjectTypeAnnotationParser(TestAnnotationReader::create()))->supports(new ObjectType()));
        self::assertTrue((new ObjectTypeAnnotationParser(TestAnnotationReader::create()))->supports(new InputObjectType()));
    }

    public function testParseSimpleObject()
    {
        $endpoint = new Endpoint('default');

        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [ObjectType::class]);

        self::assertTrue($endpoint->hasType('Post'));
        self::assertTrue($endpoint->hasTypeForClass(Post::class));
        /** @var ObjectDefinition $postDefinition */
        $postDefinition = $endpoint->getType(Post::class);
        self::assertCount(16, $postDefinition->getFields());

        //fields
        self::assertNotNull($postDefinition->getField('id'));
        self::assertNotNull($postDefinition->getField('title'));
        self::assertNotNull($postDefinition->getField('body'));
        self::assertNotNull($postDefinition->getField('comments'));
        self::assertNotNull($postDefinition->getField('date'));
        self::assertNotNull($postDefinition->getField('author'));
        self::assertNotNull($postDefinition->getField('tags'));
        self::assertNotNull($postDefinition->getField('hasTags'));
        self::assertNotNull($postDefinition->getField('containsTag'));

        self::assertEquals('Boolean', $postDefinition->getField('containsTag')->getType());
        self::assertTrue($postDefinition->getField('containsTag')->hasArgument('tag'));
        self::assertCount(1, $postDefinition->getField('containsTag')->getArguments());
        self::assertEquals('String', $postDefinition->getField('containsTag')->getArgument('tag')->getType());
        self::assertEquals('tagName', $postDefinition->getField('containsTag')->getArgument('tag')->getInternalName());

        self::assertEquals('String', $postDefinition->getField('tags')->getType());
        self::assertTrue($postDefinition->getField('tags')->isList());

        self::assertEquals('!object.getTags()', $postDefinition->getField('hasTags')->getMeta('expression'));
        self::assertEquals('Boolean', $postDefinition->getField('hasTags')->getType());

        self::assertEquals(['Node'], $postDefinition->getField('id')->getInheritedFrom());
        self::assertEquals('ID', $postDefinition->getField('id')->getType());

        self::assertEquals(['HasAuthor'], $postDefinition->getField('author')->getInheritedFrom());
        self::assertEquals('User', $postDefinition->getField('author')->getType());

        self::assertEquals(['Message'], $postDefinition->getField('body')->getInheritedFrom());
        self::assertEquals('String', $postDefinition->getField('body')->getType());

        self::assertEquals(['Commentable'], $postDefinition->getField('comments')->getInheritedFrom());
        self::assertEquals('PostComment', $postDefinition->getField('comments')->getType());

        self::assertEquals(['HasAuthor', 'Node', 'Commentable', 'Message'], $postDefinition->getInterfaces());

        //check interfaces
        self::assertTrue($endpoint->hasType('HasAuthor'));
        self::assertTrue($endpoint->hasTypeForClass(HasAuthorInterface::class));
        /** @var InterfaceDefinition $hasAuthorDefinition */
        $hasAuthorDefinition = $endpoint->getType(HasAuthorInterface::class);
        self::assertEquals(['Post' => 'Post', 'Message' => 'Message'], $hasAuthorDefinition->getImplementors());
        self::assertCount(1, $hasAuthorDefinition->getFields());
        self::assertNotNull($hasAuthorDefinition->getField('author'));
        self::assertEquals('getAuthor', $hasAuthorDefinition->getField('author')->getOriginName());
        self::assertEquals('ReflectionMethod', $hasAuthorDefinition->getField('author')->getOriginType());
        self::assertEquals('User', $hasAuthorDefinition->getField('author')->getType());

        self::assertTrue($endpoint->hasType('Message'));
        self::assertTrue($endpoint->hasTypeForClass(Message::class));
        /** @var InterfaceDefinition $messageDefinition */
        $messageDefinition = $endpoint->getType(Message::class);
        self::assertEquals(['Post' => 'Post'], $messageDefinition->getImplementors());
        self::assertCount(3, $messageDefinition->getFields());
    }

    public function testParsePolymorphicObject()
    {
        $endpoint = new Endpoint('default');

        TestDefinitionHelper::loadAnnotationDefinitions(User::class, $endpoint, [ObjectType::class]);

        /** @var InterfaceDefinition $user */
        $user = $endpoint->getType('User');
        self::assertEquals(
            [
                'Customer' => 'Customer',
                'Administrator' => 'Administrator',
            ],
            $user->getImplementors()
        );

        /** @var ObjectDefinition $customer */
        $customer = $endpoint->getType('Customer');
        self::assertEquals(['Node', 'User'], $customer->getInterfaces());

        /** @var ObjectDefinition $admin */
        $admin = $endpoint->getType('Administrator');
        self::assertEquals(['Node', 'User'], $admin->getInterfaces());

    }

    public function testParseInputObject()
    {
        $endpoint = new Endpoint('default');

        TestDefinitionHelper::loadAnnotationDefinitions(OrderBy::class, $endpoint, [InputObjectType::class]);

        /** @var ObjectDefinition $orderBy */
        $orderBy = $endpoint->getType('OrderBy');

        self::assertCount(2, $orderBy->getFields());
        self::assertEquals('String', $orderBy->getField('field')->getType());
        self::assertEquals(OrderDirectionType::class, $orderBy->getField('direction')->getType());
    }
}
