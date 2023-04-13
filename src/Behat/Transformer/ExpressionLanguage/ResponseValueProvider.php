<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Transformer\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Ynlo\GraphQLBundle\Behat\Client\GraphQLClient;

/**
 * Give access to the latest response inside expressions
 *
 * @example "{response.data.id}" => "VXNlcjox"
 */
class ResponseValueProvider implements ExpressionPreprocessorInterface
{
    /**
     * @var GraphQLClient
     */
    protected $client;

    /**
     * ResponseValueProvider constructor.
     *
     * @param GraphQLClient $client
     */
    public function __construct(GraphQLClient $client)
    {
        $this->client = $client;
    }

    public function setUp(ExpressionLanguage $el, string &$expression, array &$values)
    {
        if ($expression && $this->client->getResponse() && preg_match('/response[.) ]/', $expression)) {
            $values['response'] = json_decode($this->client->getResponse()->getContent());
        }
    }
}