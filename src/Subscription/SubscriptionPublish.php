<?php
/*
 * ******************************************************************************
 * This file is part of the GraphQL Bundle package.
 *
 * (c) YnloUltratech <support@ynloultratech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  *****************************************************************************
 */

namespace Ynlo\GraphQLBundle\Subscription;

use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Util\IDEncoder;

/**
 * message triggered when a subscription publish is called
 */
class SubscriptionPublish implements SubscriptionMessage
{
    protected string $channel;

    protected array $filters;

    protected array $data;

    /**
     * SubscriptionDispatch constructor.
     *
     * @param string $channel
     * @param array  $filters
     * @param array  $data
     */
    public function __construct(string $channel, array $filters, array $data)
    {
        $this->channel = $channel;

        array_walk_recursive(
            $filters,
            static function (&$value) {
                if ($value instanceof NodeInterface) {
                    $value = IDEncoder::encode($value);
                }
            }
        );

        $this->filters = $filters;

        array_walk_recursive(
            $data,
            static function (&$value, $key) {
                if (is_object($value)) {
                    throw new \RuntimeException(
                        sprintf('The object "%s" in key "%s" can\'t be part of publish data, only scalar values can be sent.', get_class($value), $key)
                    );
                }
            }
        );

        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
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
    public function getData(): array
    {
        return $this->data;
    }
}