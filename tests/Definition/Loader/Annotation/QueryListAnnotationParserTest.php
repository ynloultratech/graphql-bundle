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
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Annotation\QueryList;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\QueryListAnnotationParser;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Profile;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Query\Post\AllPosts;
use Ynlo\GraphQLBundle\Tests\TestAnnotationReader;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class QueryListAnnotationParserTest extends TestCase
{
    public function testSupports()
    {
        self::assertTrue((new QueryListAnnotationParser(TestAnnotationReader::create()))->supports(new QueryList()));
    }

    public function testParse()
    {
        $endpoint = new Endpoint('default');

        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [ObjectType::class]);
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [QueryList::class]);

        self::assertTrue($endpoint->hasQuery('allPosts'));

        $query = $endpoint->getQuery('allPosts');

        self::assertEquals('Post', $query->getType());
        self::assertNotEmpty($query->getMeta('pagination'));
        self::assertEquals(AllPosts::class, $query->getResolver());
        self::assertTrue($query->isList());
    }

    public function testParseNonObjectType()
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessageMatches('/CRUD operations can only be applied to valid GraphQL object types/');

        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [QueryList::class]);
    }

    public function testParseNonNode()
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessageMatches('/CRUD operations can only be applied to nodes./');

        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Profile::class, $endpoint);

        $ref = new \ReflectionClass(Profile::class);
        $reader = TestAnnotationReader::create();
        $parser = new QueryListAnnotationParser($reader);
        $parser->parse(new QueryList(), $ref, $endpoint);
    }
}
