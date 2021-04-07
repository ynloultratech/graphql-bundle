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

use Symfony\Component\HttpFoundation\Request;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Util\IDEncoder;

class Subscription
{
    protected string $id;

    protected string $channel;

    protected array $arguments = [];

    protected Request $request;

    public function __construct(string $channel, string $id, Request $request, array $arguments = [])
    {
        $this->id = $id;
        $this->channel = $channel;

        array_walk_recursive(
            $arguments,
            static function (&$value) {
                if ($value instanceof NodeInterface) {
                    $value = IDEncoder::encode($value);
                }
            }
        );

        $this->arguments = $arguments;

        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
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
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}