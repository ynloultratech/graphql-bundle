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

use Doctrine\ORM\Proxy\Proxy;
use GraphQL\Deferred;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ynlo\GraphQLBundle\Events\GraphQLEvents;
use Ynlo\GraphQLBundle\Events\GraphQLFieldEvent;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Resolver\DeferredBuffer;

/**
 * NodeDeferredListener
 */
class NodeDeferredListener implements EventSubscriberInterface
{
    /**
     * @var DeferredBuffer
     */
    private $deferredBuffer;

    /**
     *
     * @param DeferredBuffer $deferredBuffer
     */
    public function __construct(DeferredBuffer $deferredBuffer)
    {
        $this->deferredBuffer = $deferredBuffer;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            GraphQLEvents::POST_READ_FIELD => 'postReadField',
        ];
    }

    /**
     * @param GraphQLFieldEvent $event
     */
    public function postReadField(GraphQLFieldEvent $event)
    {
        $value = $event->getValue();
        if ($value instanceof Proxy && $value instanceof NodeInterface && !$value->__isInitialized()) {
            $this->deferredBuffer->add($value);

            $event->setValue(
                new Deferred(
                    function () use ($value) {
                        $this->deferredBuffer->loadBuffer();

                        return $this->deferredBuffer->getLoadedEntity($value);
                    }
                )
            );
        }
    }
}