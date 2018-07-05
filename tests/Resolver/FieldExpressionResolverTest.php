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

use PHPUnit\Framework\TestCase;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Resolver\FieldExpressionResolver;
use Ynlo\GraphQLBundle\Resolver\ResolverContext;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class FieldExpressionResolverTest extends TestCase
{

    public function testResolver()
    {
        $resolver = new FieldExpressionResolver();
        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [ObjectType::class]);

        /** @var FieldDefinition $field */
        $field = $endpoint->getType('Post')->getField('id');
        $field->setMeta('expression', 'this.getId()');

        $context = new ResolverContext($endpoint, $field);

        self::assertEquals(1, $resolver($context, new Post(1)));

        $field->removeMeta('expression');
        self::assertNull($resolver($context, new Post(1)));
    }
}
