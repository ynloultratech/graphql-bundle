<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\EventListener\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use PHPUnit\Framework\TestCase;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\EventListener\GraphQL\FieldConcurrentUsageListener;
use Ynlo\GraphQLBundle\Events\GraphQLEvents;
use Ynlo\GraphQLBundle\Events\GraphQLFieldEvent;
use Ynlo\GraphQLBundle\Events\GraphQLFieldInfo;
use Ynlo\GraphQLBundle\Resolver\FieldExecutionContext;
use Ynlo\GraphQLBundle\Resolver\QueryExecutionContext;

class FieldConcurrentUsageListenerTest extends TestCase
{
    public function testSubscribedEvennts()
    {
        self::assertEquals([GraphQLEvents::PRE_READ_FIELD => 'preReadField'], FieldConcurrentUsageListener::getSubscribedEvents());
    }

    /**
     * @dataProvider cases
     */
    public function testPreReadField($maxConcurrentUsage, $fieldName, $xceptionMessage)
    {
        $listener = new FieldConcurrentUsageListener();

        $fieldEvent = $this->createEvent($fieldName, $maxConcurrentUsage);

        self::expectException(Error::class);
        self::expectExceptionMessage($xceptionMessage);

        $maxConcurrentUsage++;
        while ($maxConcurrentUsage) {
            $listener->preReadField($fieldEvent);
            $maxConcurrentUsage--;
        }
    }

    public function cases()
    {
        return [
            [
                1,
                'name',
                'The field "name" can be fetched only once per query. This field can`t be used in a list.',
            ],
            [
                2,
                'age',
                'The field "age" can`t be fetched more than 2 times per query.',
            ],
        ];
    }

    private function createEvent($fieldName, $maxConcurrentUsage)
    {
        $object = new ObjectDefinition();
        $field = new FieldDefinition();
        $field->setName($fieldName);
        $field->setMaxConcurrentUsage($maxConcurrentUsage);
        $info = new GraphQLFieldInfo($object, $field, new ResolveInfo([]));
        $context = new FieldExecutionContext(new QueryExecutionContext(new Endpoint('default')), $object);
        $event = new GraphQLFieldEvent($info, null, [], $context, null);

        return $event;
    }
}
