<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Subscription\PubSub;

class SubscriptionMessage
{
    /**
     * @var string
     */
    protected $channel;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * SubscriptionMessage constructor.
     *
     * @param string $channel
     * @param string $id
     * @param array  $data
     * @param array  $filters
     * @param array  $meta
     */
    public function __construct(string $channel, string $id, array $data, array $filters, array $meta)
    {
        $this->channel = $channel;
        $this->id = $id;
        $this->data = $data;
        $this->filters = $filters;
        $this->meta = $meta;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
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

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }
}
