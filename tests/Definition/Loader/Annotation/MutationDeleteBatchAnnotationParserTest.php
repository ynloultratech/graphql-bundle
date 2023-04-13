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
use Ynlo\GraphQLBundle\Annotation\MutationDeleteBatch;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\MutationDeleteBatchAnnotationParser;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\DeleteBatchNodePayload;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Profile;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Mutation\Post\DeleteBatchPost;
use Ynlo\GraphQLBundle\Tests\TestAnnotationReader;

class MutationDeleteBatchAnnotationParserTest extends TestCase
{
    public function testSupports()
    {
        self::assertTrue((new MutationDeleteBatchAnnotationParser(TestAnnotationReader::create()))->supports(new MutationDeleteBatch()));
    }

    public function testParse()
    {
        $endpoint = new Endpoint('default');

        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [ObjectType::class, MutationDeleteBatch::class]);

        self::assertTrue($endpoint->hasMutation('deleteBatchPost'));

        $mutation = $endpoint->getMutation('deleteBatchPost');

        self::assertEquals(DeleteBatchNodePayload::class, $mutation->getType());
        self::assertEquals(DeleteBatchPost::class, $mutation->getResolver());
    }

    public function testParseNonObjectType()
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessageMatches('/CRUD operations can only be applied to valid GraphQL object types/');

        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [MutationDeleteBatch::class]);
    }

    public function testParseNonNode()
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessageMatches('/CRUD operations can only be applied to nodes./');

        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Profile::class, $endpoint);

        $ref = new \ReflectionClass(Profile::class);
        $reader = TestAnnotationReader::create();
        $parser = new MutationDeleteBatchAnnotationParser($reader);
        $parser->parse(new MutationDeleteBatch(), $ref, $endpoint);
    }
}
