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
 * Can use this trait with PolymorphicObjectInterface
 * to set types in runtime
 */
trait PolymorphicObjectTrait
{
    /**
     * @var string
     */
    protected $concreteType;

    public function getConcreteType(): string
    {
        return $this->concreteType;
    }

    public function setConcreteType($type)
    {
        $this->concreteType = $type;
    }
}
