<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Query\User;

use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * @GraphQL\Query(description="Get User by login", options={
 *     @GraphQL\Plugin\Endpoints(endpoints={"admin"})
 * })
 * @GraphQL\Argument(name="login", type="string!")
 */
class ByLogin
{
    /**
     * @inheritDoc
     */
    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }

}
