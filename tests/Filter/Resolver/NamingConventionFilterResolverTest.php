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

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Annotation\QueryList;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Filter\Resolver\NamingConventionFilterResolver;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Filter\Post\HasComments;
use Ynlo\GraphQLBundle\Tests\TestAnnotationReader;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class NamingConventionFilterResolverTest extends MockeryTestCase
{
    public function testResolver()
    {
        $bundle = \Mockery::mock(Bundle::class);
        $bundle->allows('getPath')->andReturn(__DIR__.'/../../Fixtures/AppBundle');
        $bundle->allows('getNamespace')->andReturn('Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle');

        $kernel = \Mockery::mock(KernelInterface::class);
        $kernel->expects('getBundles')->andReturn([$bundle]);

        $reader = TestAnnotationReader::create();

        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [ObjectType::class, QueryList::class]);

        $query = $endpoint->getQuery('allPosts');
        $post = $endpoint->getType(Post::class);

        $resolver = new NamingConventionFilterResolver($kernel, $reader);
        $resolvedFilters = $resolver->resolve($query, $post, $endpoint);

        self::assertCount(1, $resolvedFilters);

        self::assertEquals('hasComments', $resolvedFilters[0]->name);
        self::assertEquals('View only posts with or without comments', $resolvedFilters[0]->description);
        self::assertEquals(HasComments::class, $resolvedFilters[0]->resolver);
    }
}
