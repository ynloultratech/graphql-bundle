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

/**
 * Implements this interface on a object that can use more that one type
 * and you need resolve the concrete type used in runtime
 *
 * Example:
 *
 * Class User implements PolymorphicObjectInterface{
 *   public function getConcreteType(){
 *     return $this->isAdmin() : 'Administrator' ? 'Customer';
 *   }
 * }
 *
 * $user1->getConcreteType() : 'Administrator'
 * $user2->getConcreteType() : 'Customer'
 */
interface PolymorphicObjectInterface
{
    /**
     * Resolve the concrete type of current object based on object properties
     */
    public function getConcreteType(): string;
}
