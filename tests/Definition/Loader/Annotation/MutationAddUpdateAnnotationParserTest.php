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

use PHPUnit\Framework\TestCase;
use Ynlo\GraphQLBundle\Annotation\MutationAdd;
use Ynlo\GraphQLBundle\Annotation\MutationUpdate;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\MutationAddUpdateAnnotationParser;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\AddNodePayload;
use Ynlo\GraphQLBundle\Model\UpdateNodePayload;
use Ynlo\GraphQLBundle\Mutation\UpdateNode;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Profile;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Form\Input\Post\PostInput;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Mutation\Post\AddPost;
use Ynlo\GraphQLBundle\Tests\TestAnnotationReader;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class MutationAddUpdateAnnotationParserTest extends TestCase
{

    public function testSupports()
    {
        self::assertTrue((new MutationAddUpdateAnnotationParser(TestAnnotationReader::create()))->supports(new MutationAdd()));
        self::assertTrue((new MutationAddUpdateAnnotationParser(TestAnnotationReader::create()))->supports(new MutationUpdate()));
    }

    public function testParseAdd()
    {
        $endpoint = new Endpoint('default');

        TestDefinitionHelper::loadAnnotationDefinitions(AddNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [ObjectType::class, MutationAdd::class]);

        self::assertTrue($endpoint->hasMutation('addPost'));
        self::assertTrue($endpoint->hasType('AddPostPayload'));

        $mutation = $endpoint->getMutation('addPost');
        self::assertEquals(PostInput::class, $mutation->getMeta('form')['type']);
        self::assertEquals(['operation' => 'addPost'], $mutation->getMeta('form')['options']);
        self::assertEquals(AddPost::class, $mutation->getResolver());

        /** @var ObjectDefinition $payload */
        $payload = $endpoint->getType('AddPostPayload');
        self::assertEquals('Post', $payload->getField('node')->getType());
    }

    public function testParseUpdate()
    {
        $endpoint = new Endpoint('default');

        TestDefinitionHelper::loadAnnotationDefinitions(UpdateNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [ObjectType::class, MutationUpdate::class]);

        self::assertTrue($endpoint->hasMutation('updatePost'));
        self::assertTrue($endpoint->hasType('UpdatePostPayload'));

        $mutation = $endpoint->getMutation('updatePost');
        self::assertEquals(PostInput::class, $mutation->getMeta('form')['type']);
        self::assertEquals(['operation' => 'updatePost'], $mutation->getMeta('form')['options']);
        self::assertEquals(UpdateNode::class, $mutation->getResolver());

        /** @var ObjectDefinition $payload */
        $payload = $endpoint->getType('UpdatePostPayload');
        self::assertEquals('Post', $payload->getField('node')->getType());
    }

    public function testParseNonObjectType()
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessageMatches('/CRUD operations can only be applied to valid GraphQL object types/');

        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [MutationUpdate::class]);
    }

    public function testParseNonNode()
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessageMatches('/CRUD operations can only be applied to nodes./');

        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Profile::class, $endpoint);

        $ref = new \ReflectionClass(Profile::class);
        $reader = TestAnnotationReader::create();
        $parser = new MutationAddUpdateAnnotationParser($reader);
        $parser->parse(new MutationAdd(), $ref, $endpoint);
    }
}
