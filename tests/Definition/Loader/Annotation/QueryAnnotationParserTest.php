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
use Ynlo\GraphQLBundle\Annotation\Argument;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Annotation\Query;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\QueryAnnotationParser;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\User;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Query\User\ByLogin;
use Ynlo\GraphQLBundle\Tests\TestAnnotationReader;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class QueryAnnotationParserTest extends TestCase
{
    public function testSupports()
    {
        self::assertTrue((new QueryAnnotationParser(TestAnnotationReader::create()))->supports(new Query()));
    }

    public function testParse()
    {
        $endpoint = new Endpoint('default');

        TestDefinitionHelper::loadAnnotationDefinitions(User::class, $endpoint, [ObjectType::class]);
        TestDefinitionHelper::loadAnnotationDefinitions(ByLogin::class, $endpoint, [Query::class]);

        self::assertTrue($endpoint->hasQuery('byLogin'));

        $query = $endpoint->getQuery('byLogin');

        self::assertEquals('User', $query->getType());
        self::assertEquals(ByLogin::class, $query->getResolver());
        self::assertEquals(['endpoints' => ['admin']], $query->getMeta('endpoints'));

        self::assertEquals('String', $query->getArgument('login')->getType());
        self::assertTrue($query->getArgument('login')->isNonNull());
    }

    public function testParseQueryWithTypeAndInlineArguments()
    {
        $endpoint = new Endpoint('default');

        TestDefinitionHelper::loadAnnotationDefinitions(User::class, $endpoint, [ObjectType::class]);

        $ref = new \ReflectionClass(ByLogin::class);
        $reader = TestAnnotationReader::create();
        /** @var Query $annotation */
        $annotation = $reader->getClassAnnotation($ref, Query::class);
        $annotation->type = 'User';

        $loginArg = new Argument();
        $loginArg->name = 'username';
        $loginArg->type = 'string!';

        $annotation->arguments = [$loginArg];
        $parser = new QueryAnnotationParser($reader);
        $parser->parse($annotation, $ref, $endpoint);

        self::assertTrue($endpoint->hasQuery('byLogin'));

        $query = $endpoint->getQuery('byLogin');

        self::assertEquals('User', $query->getType());
        self::assertEquals(ByLogin::class, $query->getResolver());
        self::assertEquals(['endpoints' => ['admin']], $query->getMeta('endpoints'));

        self::assertEquals('String', $query->getArgument('username')->getType());
        self::assertTrue($query->getArgument('username')->isNonNull());
    }
}
