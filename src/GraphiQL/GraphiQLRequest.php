<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\GraphiQL;

/**
 * GraphiQLRequest
 */
class GraphiQLRequest
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * GraphiQLRequest constructor.
     *
     * @param string $url
     * @param array  $params
     * @param array  $headers
     */
    public function __construct(string $url, array $params = [], array $headers = [])
    {
        $this->url = $url;
        $this->params = $params;
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url.'?'.http_build_query($this->params);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function addParameter(string $name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function addHeader(string $name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
