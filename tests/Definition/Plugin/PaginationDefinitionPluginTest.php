<?php
/*
 * ******************************************************************************
 * This file is part of the GraphQL Bundle package.
 *
 * (c) YnloUltratech <support@ynloultratech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  *****************************************************************************
 */

namespace Ynlo\GraphQLBundle\Tests\Definition\Plugin;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Ynlo\GraphQLBundle\Annotation\InputObjectType;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Annotation\QueryList;
use Ynlo\GraphQLBundle\Definition\EnumDefinition;
use Ynlo\GraphQLBundle\Definition\EnumValueDefinition;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Plugin\PaginationDefinitionPlugin;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Filter\FilterFactory;
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\OrderBy\Common\OrderByRelatedField;
use Ynlo\GraphQLBundle\OrderBy\Common\OrderBySimpleField;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\OrderBy\Post\OrderByUser;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class PaginationDefinitionPluginTest extends MockeryTestCase
{
    public function testConfigure()
    {
        $filtersFactory = \Mockery::mock(FilterFactory::class);
        $filtersFactory->expects('build');

        $plugin = new PaginationDefinitionPlugin($filtersFactory, []);

        $endpoint = new Endpoint('default');

        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [ObjectType::class, QueryList::class]);
        TestDefinitionHelper::loadAnnotationDefinitions(OrderBy::class, $endpoint, [InputObjectType::class]);

        $definition = $endpoint->getQuery('allPosts');

        $config = $plugin->normalizeConfig($definition, $definition->getMeta('pagination'));

        $plugin->configure($definition, $endpoint, $config);

        self::assertTrue($definition->hasArgument('search'));
        self::assertTrue($definition->hasArgument('first'));
        self::assertTrue($definition->hasArgument('last'));
        self::assertTrue($definition->hasArgument('after'));
        self::assertTrue($definition->hasArgument('before'));
        self::assertTrue($definition->hasArgument('page'));
        self::assertTrue($definition->hasArgument('order'));

        // TEST Order

        /** @var InputObjectDefinition $postOrderBy */
        $postOrderBy = $endpoint->getType('PostOrderBy');
        self::assertTrue($postOrderBy->hasField('field'));
        self::assertEquals('PostOrderByField', $postOrderBy->getField('field')->getType());
        self::assertTrue($postOrderBy->hasField('direction'));

        /** @var EnumDefinition $postOrderByField */
        $postOrderByField = $endpoint->getType('PostOrderByField');

        /** @var EnumValueDefinition[] $values */
        $values = $postOrderByField->getValues();

        self::assertCount(12, $values);

        self::assertEquals(OrderBySimpleField::class, $values['title']->getMeta('resolver'));
        self::assertEquals('title', $values['title']->getMeta('field'));

        self::assertEquals(OrderBySimpleField::class, $values['body']->getMeta('resolver'));
        self::assertEquals('body', $values['body']->getMeta('field'));

        self::assertEquals(OrderBySimpleField::class, $values['date']->getMeta('resolver'));
        self::assertEquals('date', $values['date']->getMeta('field'));

        self::assertEquals(OrderBySimpleField::class, $values['id']->getMeta('resolver'));
        self::assertEquals('id', $values['id']->getMeta('field'));

        self::assertEquals(OrderBySimpleField::class, $values['topic']->getMeta('resolver'));
        self::assertEquals('topic', $values['topic']->getMeta('field'));

        self::assertEquals(OrderBySimpleField::class, $values['status']->getMeta('resolver'));
        self::assertEquals('status', $values['status']->getMeta('field'));

        self::assertEquals(OrderBySimpleField::class, $values['type']->getMeta('resolver'));
        self::assertEquals('type', $values['type']->getMeta('field'));

        self::assertEquals(OrderBySimpleField::class, $values['private']->getMeta('resolver'));
        self::assertEquals('private', $values['private']->getMeta('field'));

        self::assertEquals(OrderBySimpleField::class, $values['views']->getMeta('resolver'));
        self::assertEquals('views', $values['views']->getMeta('field'));

        self::assertEquals(OrderByRelatedField::class, $values['category']->getMeta('resolver'));
        self::assertEquals('category.name', $values['category']->getMeta('field'));

        self::assertEquals(OrderByUser::class, $values['user']->getMeta('resolver'));
        self::assertEquals('user', $values['user']->getMeta('field'));
    }
}
