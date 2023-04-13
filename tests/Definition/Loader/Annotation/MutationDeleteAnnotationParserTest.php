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
use Ynlo\GraphQLBundle\Annotation\MutationDelete;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\MutationDeleteAnnotationParser;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\DeleteNodePayload;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Profile;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Mutation\Post\DeletePost;
use Ynlo\GraphQLBundle\Tests\TestAnnotationReader;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class MutationDeleteAnnotationParserTest extends TestCase
{
    public function testSupports()
    {
        self::assertTrue((new MutationDeleteAnnotationParser(TestAnnotationReader::create()))->supports(new MutationDelete()));
    }

    public function testParse()
    {
        $endpoint = new Endpoint('default');

        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [ObjectType::class, MutationDelete::class]);

        self::assertTrue($endpoint->hasMutation('deletePost'));

        $mutation = $endpoint->getMutation('deletePost');

        self::assertEquals(DeleteNodePayload::class, $mutation->getType());
        self::assertEquals(DeletePost::class, $mutation->getResolver());
    }

    public function testParseNonObjectType()
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessageMatches('/CRUD operations can only be applied to valid GraphQL object types/');

        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [MutationDelete::class]);
    }

    public function testParseNonNode()
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessageMatches('/CRUD operations can only be applied to nodes./');

        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Profile::class, $endpoint);

        $ref = new \ReflectionClass(Profile::class);
        $reader = TestAnnotationReader::create();
        $parser = new MutationDeleteAnnotationParser($reader);
        $parser->parse(new MutationDelete(), $ref, $endpoint);
    }
}
