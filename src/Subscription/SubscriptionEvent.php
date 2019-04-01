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

use Ynlo\GraphQLBundle\Model\PolymorphicObjectInterface;
use Ynlo\GraphQLBundle\Model\PolymorphicObjectTrait;

class SubscriptionEvent implements PolymorphicObjectInterface
{
    use PolymorphicObjectTrait;

    /**
     * @var mixed|null
     */
    protected $data;

    /**
     * SubscriptionEvent constructor.
     *
     * @param mixed|null $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        $this->data = $value;
    }

    /**
     * @inheritDoc
     */
    public function __isset($name)
    {
        return isset($this->data);
    }
}
