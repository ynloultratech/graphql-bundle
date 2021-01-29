<?php

/**
 * This file is part of the GenericApi package.
 *
 * (c) RafaelSR <https://github.com/rafrsr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ynlo\GraphQLBundle\Events;

use Symfony\Component\EventDispatcher\Event as BaseEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event as ContractsBaseEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal proxy event to add support for symfony 5
 */
if (is_subclass_of(EventDispatcher::class, EventDispatcherInterface::class)) {
    class GraphQLEventProxy extends ContractsBaseEvent
    {
    }
} else {
    class GraphQLEventProxy extends BaseEvent
    {
    }
}
