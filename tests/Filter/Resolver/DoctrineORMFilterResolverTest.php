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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Ynlo\GraphQLBundle\Annotation\InputObjectType;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Annotation\QueryList;
use Ynlo\GraphQLBundle\Definition\EnumDefinition;
use Ynlo\GraphQLBundle\Definition\EnumValueDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Filter\Common\ArrayFilter;
use Ynlo\GraphQLBundle\Filter\Common\BooleanFilter;
use Ynlo\GraphQLBundle\Filter\Common\DateFilter;
use Ynlo\GraphQLBundle\Filter\Common\EnumFilter;
use Ynlo\GraphQLBundle\Filter\Common\NodeFilter;
use Ynlo\GraphQLBundle\Filter\Common\NumberFilter;
use Ynlo\GraphQLBundle\Filter\Common\StringFilter;
use Ynlo\GraphQLBundle\Filter\Resolver\DoctrineORMFilterResolver;
use Ynlo\GraphQLBundle\Model\Filter\ArrayComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\DateTimeComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\EnumComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\FloatComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\IntegerComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\NodeComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\StringComparisonExpression;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Category;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Topic;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Types\PostStatusType;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Types\PostType;
use Ynlo\GraphQLBundle\Tests\TestAnnotationReader;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;
use Ynlo\GraphQLBundle\Type\Types;

class DoctrineORMFilterResolverTest extends MockeryTestCase
{
    public function testResolve()
    {
        $manager = \Mockery::mock(EntityManagerInterface::class);
        $manager
            ->expects('getClassMetadata')
            ->with(Post::class)
            ->andReturnUsing(
                function () {
                    $postMetadata = new ClassMetadata(Post::class);
                    $annotationDriver = new AnnotationDriver(TestAnnotationReader::create());
                    $annotationDriver->loadMetadataForClass(Post::class, $postMetadata);

                    return $postMetadata;
                }
            );

        $manager
            ->expects('getClassMetadata')
            ->with(Category::class)
            ->andReturnUsing(
                function () {
                    $postMetadata = new ClassMetadata(Category::class);
                    $annotationDriver = new AnnotationDriver(TestAnnotationReader::create());
                    $annotationDriver->loadMetadataForClass(Category::class, $postMetadata);

                    return $postMetadata;
                }
            );

        $manager
            ->expects('getClassMetadata')
            ->with(Topic::class)
            ->andReturnUsing(
                function () {
                    $postMetadata = new ClassMetadata(Topic::class);
                    $annotationDriver = new AnnotationDriver(TestAnnotationReader::create());
                    $annotationDriver->loadMetadataForClass(Topic::class, $postMetadata);

                    return $postMetadata;
                }
            );

        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [ObjectType::class, QueryList::class]);
        TestDefinitionHelper::loadAnnotationDefinitions(Category::class, $endpoint, [ObjectType::class]);
        TestDefinitionHelper::loadAnnotationDefinitions(Topic::class, $endpoint, [ObjectType::class]);
        TestDefinitionHelper::loadAnnotationDefinitions(EnumComparisonExpression::class, $endpoint, [InputObjectType::class]);
        TypeRegistry::addTypeMapping('PostStatus', PostStatusType::class);

        $postTypeEnum = new EnumDefinition();
        $postTypeEnum->setName('PostType');
        $postTypeEnum->addValue(new EnumValueDefinition(PostType::ARTICLE));
        $postTypeEnum->addValue(new EnumValueDefinition(PostType::PAGE));
        $endpoint->addType($postTypeEnum);

        $query = $endpoint->getQuery('allPosts');
        $post = $endpoint->getType(Post::class);

        $resolver = new DoctrineORMFilterResolver($manager);
        $resolvedFilters = $resolver->resolve($query, $post, $endpoint);

        self::assertCount(11, $resolvedFilters);

        $expected = [
            'body' => [
                'resolver' => StringFilter::class,
                'field' => 'body',
                'type' => StringComparisonExpression::class,
            ],
            'title' => [
                'resolver' => StringFilter::class,
                'field' => 'title',
                'type' => StringComparisonExpression::class,
            ],
            'date' => [
                'resolver' => DateFilter::class,
                'field' => 'date',
                'type' => DateTimeComparisonExpression::class,
            ],
            'private' => [
                'resolver' => BooleanFilter::class,
                'field' => 'private',
                'type' => Types::BOOLEAN,
            ],
            'views' => [
                'resolver' => NumberFilter::class,
                'field' => 'views',
                'type' => IntegerComparisonExpression::class,
            ],
            'tags' => [
                'resolver' => ArrayFilter::class,
                'field' => 'tags',
                'type' => ArrayComparisonExpression::class,
            ],
            'status' => [
                'resolver' => EnumFilter::class,
                'field' => 'status',
                'type' => 'PostStatusComparisonExpression',
            ],
            'type' => [
                'resolver' => EnumFilter::class,
                'field' => 'type',
                'type' => 'PostTypeComparisonExpression',
            ],
            'rate' => [
                'resolver' => NumberFilter::class,
                'field' => 'rate',
                'type' => FloatComparisonExpression::class,
            ],
            'topic' => [
                'resolver' => NodeFilter::class,
                'field' => 'topic',
                'type' => NodeComparisonExpression::class,
            ],
            'categories' => [
                'resolver' => NodeFilter::class,
                'field' => 'categories',
                'type' => NodeComparisonExpression::class,
            ],
        ];

        $tested = [];
        foreach ($resolvedFilters as $resolvedFilter) {
            if (!isset($expected[$resolvedFilter->name])) {
                continue;
            }
            $tested[$resolvedFilter->name] = $expected[$resolvedFilter->name];
            self::assertEquals($expected[$resolvedFilter->name]['resolver'], $resolvedFilter->resolver);
            self::assertEquals($expected[$resolvedFilter->name]['field'], $resolvedFilter->field);
            self::assertEquals($expected[$resolvedFilter->name]['type'], $resolvedFilter->type);
        }
        self::assertEquals($expected, $tested);

        self::assertTrue($endpoint->hasType('PostStatusComparisonExpression'));
        self::assertEquals('PostStatus', $endpoint->getType('PostStatusComparisonExpression')->getField('values')->getType());

        self::assertTrue($endpoint->hasType('PostTypeComparisonExpression'));
        self::assertEquals('PostType', $endpoint->getType('PostTypeComparisonExpression')->getField('values')->getType());
    }
}
