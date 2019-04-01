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
 * The subscriptions request is launched when
 * internally the subscriptions manager dispatch subscriptions
 * in order to emulate user like requests
 */
class SubscriptionRequest
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * SubscriptionRequest constructor.
     *
     * @param string $id
     * @param array  $data
     */
    public function __construct(string $id, array $data = [])
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
