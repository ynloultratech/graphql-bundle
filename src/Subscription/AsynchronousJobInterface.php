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

/**
 * Implements this interface on a subscription that queue a high costly task
 * in order to run the onSubscribe action before the __invoke()
 *
 * NOTE: the __invoke method will be executed async when the subscription is dispatched
 * but the onSubscribe is dispatched when the user is subscribed
 */
interface AsynchronousJobInterface
{
    /**
     * Subscriptions arguments are sent in the same
     * order of the __invoke method, then can use func_get_args()
     * to get all arguments
     *
     * @return void
     */
    public function onSubscribe(): void;
}
