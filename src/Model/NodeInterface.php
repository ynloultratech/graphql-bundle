<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Model;

use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * @GraphQL\InterfaceType(description="An object with an ID.")
 */
interface NodeInterface
{
    /**
     * @GraphQL\Field(type="ID!")
     *
     * @return mixed
     */
    public function getId(): ?Int;
}
