<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Resolver;

use Ynlo\GraphQLBundle\Resolver\AbstractResolver;

class CustomResolver extends AbstractResolver
{
    /**
     * @inheritDoc
     */
    public function __invoke($root, $args = [])
    {

    }
}
