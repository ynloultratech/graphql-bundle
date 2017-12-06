<?php

/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\ApiDemoBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Model\TimestampableInterface;

/**
 * Class TimestampableSubscriber.
 */
class TimestampableSubscriber implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist => 'prePersist',
            Events::preUpdate => 'preUpdate',
        ];
    }

    /**
     * @param LifecycleEventArgs $event the event
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $object = $event->getObject();
        if ($object instanceof TimestampableInterface) {
            $object->setCreatedAt(new \DateTime());
            $object->setUpdatedAt(new \DateTime());
        }
    }

    /**
     * @param LifecycleEventArgs $event the event
     */
    public function preUpdate(LifecycleEventArgs $event)
    {
        $object = $event->getObject();
        if ($object instanceof TimestampableInterface) {
            $object->setUpdatedAt(new \DateTime());
        }
    }
}
