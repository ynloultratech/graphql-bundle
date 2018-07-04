<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\EventListener\GraphQL;

use GraphQL\Error\Error;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Events\GraphQLEvents;
use Ynlo\GraphQLBundle\Events\GraphQLFieldEvent;

class FieldConcurrentUsageListener implements EventSubscriberInterface
{
    /**
     * @var int[]
     */
    private static $concurrentUsages = [];

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            GraphQLEvents::OPERATION_START => 'operationStart',
            GraphQLEvents::PRE_READ_FIELD => 'preReadField',
        ];
    }

    public function operationStart()
    {
        self::$concurrentUsages = [];
    }

    /**
     * @param GraphQLFieldEvent $event
     */
    public function preReadField(GraphQLFieldEvent $event)
    {
        $definition = $event->getContext()->getDefinition();
        if ($definition instanceof FieldDefinition && $maxConcurrentUsage = $definition->getMaxConcurrentUsage()) {
            $oid = spl_object_hash($definition);
            $usages = static::$concurrentUsages[$oid] ?? 1;
            if ($usages > $maxConcurrentUsage) {
                if (1 === $maxConcurrentUsage) {
                    $error = sprintf(
                        'The field "%s" can be fetched only once per query. This field can`t be used in a list.',
                        $definition->getName()
                    );
                } else {
                    $error = sprintf(
                        'The field "%s" can`t be fetched more than %s times per query.',
                        $definition->getName(),
                        $maxConcurrentUsage
                    );
                }
                throw new Error($error);
            }
            static::$concurrentUsages[$oid] = $usages + 1;
        }
    }
}
