<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ynlo\GraphQLBundle\Model\ID;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * @method Client getClient()
 */
trait GraphQLHelperTrait
{
    /**
     * Whether to insulate each request or not.
     * The request ran into a separate process to avoid
     * any conflict (cache, container, doctrine etc..) with previous requests.
     *
     * NOTE: should be disabled for debugging
     *
     * @var bool
     */
    protected static $insulateRequests = true;

    /**
     * Endpoint where the API is available, e.g. /api
     *
     * @var string
     */
    protected static $endpoint = '';

    private static $query;

    /**
     * @param string    $query     query in GraphQL format
     * @param array     $variables array of variables
     * @param bool|null $insulate  Whether to insulate the requests or not. Leave null to use default value.
     *                             The request ran into a separate process to avoid
     *                             any conflict (cache, container, doctrine etc..) with previous requests.
     *                             NOTE: should be disabled for debugging
     */
    protected static function send($query, array $variables = [], $insulate = null)
    {
        self::$query = ['query' => $query, 'variables' => $variables];

        if (null === $insulate) {
            $insulate = static::$insulateRequests;
        }

        $client = static::getClient();
        $client->insulate($insulate);
        $client->request(Request::METHOD_POST, self::$endpoint, [], [], [], json_encode(self::$query));
    }

    /**
     * @param string               $nodeType
     * @param string|NodeInterface $databaseId
     *
     * @return string
     */
    protected static function encodeID($nodeType, $databaseId)
    {
        if ($databaseId instanceof NodeInterface) {
            $databaseId = $databaseId->getId();
        }

        return ID::encode($nodeType, $databaseId);
    }

    /**
     * @param string $globalID
     *
     * @return ID
     */
    protected static function decodeID($globalID): ID
    {
        return ID::createFromString($globalID);
    }

    /**
     * Print helpful debug information
     */
    protected static function debugInfo()
    {
        if (self::$query) {
            $query = self::$query['query'] ?? null;

            $type = 'QUERY';
            if (preg_match('/^\s*mutation/', $query)) {
                $type = 'MUTATION';
            }

            $variables = self::$query['variables'] ?? null;

            print_r("\n\n-------------- GraphQL $type ----------------\n\n");
            print_r($query ?? null);
            print_r("\n\n");
            print_r("------------------- VARIABLES-----------------------\n\n");
            print_r(json_encode($variables, JSON_PRETTY_PRINT));
            print_r("\n\n");
            print_r("-------------------- RESPONSE ----------------------\n\n");

            /** @var Response $response */
            $response = static::getClient()->getResponse();
            print_r(sprintf("STATUS: [%s] %s \n\n", $response->getStatusCode(), Response::$statusTexts[$response->getStatusCode()] ?? 'Unknown Status'));

            $content = $response->getContent();
            $json = @json_decode($content, true);
            if ($json) {
                print_r(json_encode($json, JSON_PRETTY_PRINT));
            } else {
                print_r($content);
            }
            print_r("\n\n");
            print_r("-----------------------------------------------------\n\n");
        }
    }
}
