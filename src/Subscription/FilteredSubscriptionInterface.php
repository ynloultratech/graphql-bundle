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
 * Implements this interface on a subscriptions
 * that have filters to apply automatically or modify
 * given filters in runtime
 */
interface FilteredSubscriptionInterface
{
    public function getFilters(): array;
}
