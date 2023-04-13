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
 * Allow the use of client query variables in expressions
 * helpful to compare sent values with response
 *
 * @example "{variables.id}" => "VXNlcjox"
 * @example "{variables.input.name}" => "admin"
 */
class ClientVariablesValueProvider implements ExpressionPreprocessorInterface
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
        if ($expression && $this->client->getVariables() && preg_match('/variables\./', $expression)) {
            //encode and decode to allow access using "variables.name" instead of "variables[name]"
            $values['variables'] = json_decode(json_encode($this->client->getVariables()));
        }
    }
}