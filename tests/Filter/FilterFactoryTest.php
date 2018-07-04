<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Filter;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Ynlo\GraphQLBundle\Annotation\Filter;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Filter\Common\DateFilter;
use Ynlo\GraphQLBundle\Filter\Common\StringFilter;
use Ynlo\GraphQLBundle\Filter\FilterFactory;
use Ynlo\GraphQLBundle\Filter\FilterResolverInterface;
use Ynlo\GraphQLBundle\Model\Filter\DateComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\StringComparisonExpression;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class FilterFactoryTest extends MockeryTestCase
{
    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var FilterFactory
     */
    protected $factory;

    /**
     * @var ObjectDefinition
     */
    protected $node;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $titleFilter = new Filter();
        $titleFilter->name = 'title';
        $titleFilter->resolver = StringFilter::class;
        $titleFilter->type = StringComparisonExpression::class;
        $titleFilter->field = 'title';

        $bodyFilter = new Filter();
        $bodyFilter->name = 'body';
        $bodyFilter->resolver = StringFilter::class;
        $bodyFilter->type = StringComparisonExpression::class;
        $bodyFilter->field = 'body';

        $dateFilter = new Filter();
        $dateFilter->name = 'date';
        $dateFilter->resolver = DateFilter::class;
        $dateFilter->type = DateComparisonExpression::class;
        $dateFilter->field = 'date';

        $this->filters = [$titleFilter, $dateFilter, $bodyFilter];

        $this->endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $this->endpoint, [ObjectType::class]);

        /** @var ObjectDefinition $postDefinition */
        $this->node = $this->endpoint->getType(Post::class);

        $resolver = \Mockery::mock(FilterResolverInterface::class);
        $resolver->expects('resolve')->andReturn($this->filters);

        $this->factory = new FilterFactory([$resolver]);
    }

    public function testBuild()
    {
        $query = new QueryDefinition();
        $query->setName('allPosts');
        $query->setNode('Post');
        $query->setMeta('pagination', ['filters' => ['*', 'date' => false]]);

        $this->factory->build($query, $this->node, $this->endpoint);

        self::assertTrue($query->hasArgument('where'));
        self::assertEquals('PostCondition', $query->getArgument('where')->getType());

        /** @var ObjectDefinition $condition */
        $condition = $this->endpoint->getType('PostCondition');

        self::assertCount(2, $condition->getFields());
        self::assertEquals(StringComparisonExpression::class, $condition->getField('title')->getType());
        self::assertEquals(StringFilter::class, $condition->getField('title')->getResolver());
        self::assertEquals('title', $condition->getField('title')->getMeta('filter_field'));

        self::assertEquals(StringComparisonExpression::class, $condition->getField('body')->getType());
        self::assertEquals(StringFilter::class, $condition->getField('body')->getResolver());
        self::assertEquals('body', $condition->getField('body')->getMeta('filter_field'));
    }

    public function testBuildExplicitInclusion()
    {
        $query = new QueryDefinition();
        $query->setName('allPosts');
        $query->setNode('Post');
        $query->setMeta('pagination', ['filters' => ['*' => false, 'title, body' => true]]);

        $this->factory->build($query, $this->node, $this->endpoint);

        self::assertTrue($query->hasArgument('where'));
        self::assertEquals('PostCondition', $query->getArgument('where')->getType());

        /** @var ObjectDefinition $condition */
        $condition = $this->endpoint->getType('PostCondition');

        self::assertCount(2, $condition->getFields());
        self::assertEquals(StringComparisonExpression::class, $condition->getField('title')->getType());
        self::assertEquals(StringFilter::class, $condition->getField('title')->getResolver());
        self::assertEquals('title', $condition->getField('title')->getMeta('filter_field'));

        self::assertEquals(StringComparisonExpression::class, $condition->getField('body')->getType());
        self::assertEquals(StringFilter::class, $condition->getField('body')->getResolver());
        self::assertEquals('body', $condition->getField('body')->getMeta('filter_field'));
    }
}
