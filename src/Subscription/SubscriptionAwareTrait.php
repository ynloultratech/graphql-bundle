<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Subscription;

trait SubscriptionAwareTrait
{
    /**
     * @var Publisher
     */
    protected $publisher;

    /**
     * @param Publisher $publisher
     */
    public function setPublisher(Publisher $publisher): void
    {
        $this->publisher = $publisher;
    }
}