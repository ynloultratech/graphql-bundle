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
use Ynlo\GraphQLBundle\Annotation\Field;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\StandaloneFieldParser;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Query\User\Field\Comments;
use Ynlo\GraphQLBundle\Tests\TestAnnotationReader;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class StandaloneFieldParserTest extends MockeryTestCase
{
    public function testSupport()
    {
        self::assertTrue((new StandaloneFieldParser(TestAnnotationReader::create()))->supports(new Field()));
    }

    public function testParse()
    {
        $endpoint = new Endpoint('default');

        $comment = new ObjectDefinition();
        $comment->setName('PostComment');

        $user = new InterfaceDefinition();
        $user->setName('User');
        $user->addImplementor('Customer');

        $customer = new ObjectDefinition();
        $customer->setName('Customer');

        $endpoint->add($comment);
        $endpoint->add($user);
        $endpoint->add($customer);

        TestDefinitionHelper::loadAnnotationDefinitions(Comments::class, $endpoint, [Field::class]);

        $field = $user->getField('comments');

        //check field definition
        self::assertEquals(Comments::class, $field->getResolver());
        self::assertEquals('The comment list has filter by user and pagination.', $field->getDeprecationReason());
        self::assertEquals('Get list of comments of user', $field->getDescription());
        self::assertEquals("has_role('ROLE_ADMIN')", $field->getMeta('access_control')['expression']);

        //check arguments
        self::assertEquals('Int', $field->getArgument('limit')->getType());

        //if the field belongs to interface should be copied to implementors
        self::assertEquals($field, $customer->getField('comments'));
    }
}
