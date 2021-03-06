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
use PHPUnit\Framework\TestCase;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\EventListener\GraphQL\FieldConcurrentUsageListener;
use Ynlo\GraphQLBundle\Events\GraphQLEvents;
use Ynlo\GraphQLBundle\Events\GraphQLFieldEvent;
use Ynlo\GraphQLBundle\Resolver\ContextBuilder;

class FieldConcurrentUsageListenerTest extends TestCase
{
    public function testSubscribedEvennts()
    {
        self::assertEquals(
            [
                GraphQLEvents::OPERATION_START => 'operationStart',
                GraphQLEvents::PRE_READ_FIELD => 'preReadField',
            ],
            FieldConcurrentUsageListener::getSubscribedEvents()
        );
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
        $field = new FieldDefinition();
        $field->setName($fieldName);
        $field->setMaxConcurrentUsage($maxConcurrentUsage);

        $context = ContextBuilder::create(new Endpoint('default'))
                                 ->setDefinition($field)
                                 ->build();

        return new GraphQLFieldEvent($context, null);
    }
}
