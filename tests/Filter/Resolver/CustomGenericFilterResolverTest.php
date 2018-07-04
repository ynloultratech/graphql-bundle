<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Filter\Resolver;

use PHPUnit\Framework\TestCase;
use Ynlo\GraphQLBundle\Annotation\Filter;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Annotation\QueryList;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Filter\Resolver\CustomGenericFilterResolver;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Filter\LikeFilter;
use Ynlo\GraphQLBundle\Tests\TestAnnotationReader;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class CustomGenericFilterResolverTest extends TestCase
{
    public function testResolver()
    {
        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [ObjectType::class, QueryList::class]);

        $query = $endpoint->getQuery('allPosts');

        $stringFilter = new Filter();
        $stringFilter->type = 'string';
        $stringFilter->resolver = LikeFilter::class;

        $query->setMeta(
            'pagination',
            [
                'filters' => [
                    '*',
                    'title,body' => $stringFilter,
                    'details' => LikeFilter::class,
                ],
            ]
        );
        $post = $endpoint->getType(Post::class);

        $resolver = new CustomGenericFilterResolver(TestAnnotationReader::create());
        $resolvedFilters = $resolver->resolve($query, $post, $endpoint);

        self::assertCount(3, $resolvedFilters);

        self::assertEquals('title', $resolvedFilters[0]->name);
        self::assertEquals('title', $resolvedFilters[0]->field);
        self::assertEquals('string', $resolvedFilters[0]->type);
        self::assertEquals(LikeFilter::class, $resolvedFilters[0]->resolver);

        self::assertEquals('body', $resolvedFilters[1]->name);
        self::assertEquals('body', $resolvedFilters[1]->field);
        self::assertEquals('string', $resolvedFilters[1]->type);
        self::assertEquals(LikeFilter::class, $resolvedFilters[1]->resolver);

        self::assertEquals('details', $resolvedFilters[2]->name);
        self::assertEquals('details', $resolvedFilters[2]->field);
        self::assertEquals('string', $resolvedFilters[2]->type);
        self::assertEquals(LikeFilter::class, $resolvedFilters[2]->resolver);
    }
}
