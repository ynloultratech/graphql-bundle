<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Model;

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Demo\AppBundle\Entity\User;

/**
 * @GraphQL\InterfaceType()
 */
interface HasAuthorInterface
{
    /**
     * @GraphQL\Field(type="User")
     *
     * @return null|User
     */
    public function getAuthor():?User;

    /**
     * @param User $user
     *
     * @return HasAuthorInterface
     */
    public function setAuthor(User $user): HasAuthorInterface;
}
