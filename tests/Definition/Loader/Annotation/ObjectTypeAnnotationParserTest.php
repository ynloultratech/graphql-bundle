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
        /** @var ObjectDefinition $commentDefinition */
        $commentDefinition = $endpoint->getType(Post::class);
        self::assertCount(9, $commentDefinition->getFields());

        //fields
        self::assertNotNull($commentDefinition->getField('id'));
        self::assertNotNull($commentDefinition->getField('title'));
        self::assertNotNull($commentDefinition->getField('body'));
        self::assertNotNull($commentDefinition->getField('comments'));
        self::assertNotNull($commentDefinition->getField('date'));
        self::assertNotNull($commentDefinition->getField('author'));
        self::assertNotNull($commentDefinition->getField('tags'));
        self::assertNotNull($commentDefinition->getField('hasTags'));
        self::assertNotNull($commentDefinition->getField('containsTag'));

        self::assertEquals('Boolean', $commentDefinition->getField('containsTag')->getType());
        self::assertTrue($commentDefinition->getField('containsTag')->hasArgument('tag'));
        self::assertCount(1, $commentDefinition->getField('containsTag')->getArguments());
        self::assertEquals('String', $commentDefinition->getField('containsTag')->getArgument('tag')->getType());
        self::assertEquals('tagName', $commentDefinition->getField('containsTag')->getArgument('tag')->getInternalName());

        self::assertEquals('String', $commentDefinition->getField('tags')->getType());
        self::assertTrue($commentDefinition->getField('tags')->isList());

        self::assertEquals('!object.getTags()', $commentDefinition->getField('hasTags')->getMeta('expression'));
        self::assertEquals('Boolean', $commentDefinition->getField('hasTags')->getType());

        self::assertEquals(['Node'], $commentDefinition->getField('id')->getInheritedFrom());
        self::assertEquals('ID', $commentDefinition->getField('id')->getType());

        self::assertEquals(['HasAuthor'], $commentDefinition->getField('author')->getInheritedFrom());
        self::assertEquals('User', $commentDefinition->getField('author')->getType());

        self::assertEquals(['Message'], $commentDefinition->getField('body')->getInheritedFrom());
        self::assertEquals('String', $commentDefinition->getField('body')->getType());

        self::assertEquals(['HasAuthor', 'Node', 'Message'], $commentDefinition->getInterfaces());

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
