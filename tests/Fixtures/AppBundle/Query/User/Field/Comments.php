<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Query\User\Field;

use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * @GraphQL\Field(type="[Comment]",
 *     description="Get list of comments of user",
 *     deprecationReason="The comment list has filter by user and pagination.",
 *     options={
 *          @GraphQL\Plugin\AccessControl(expression="has_role('ROLE_ADMIN')")
 *     })
 * @GraphQL\Argument(
 *     name="limit",
 *     type="int",
 *     description="Max number of comments to fetch",
 * )
 */
class Comments
{
    /**
     * @inheritDoc
     */
    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }
}
