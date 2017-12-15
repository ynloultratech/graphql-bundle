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
use Ynlo\GraphQLBundle\Model\ID;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * @method Client getClient()
 */
trait GraphQLHelperTrait
{
    private static $endpoint;

    private static $query;

    /**
     * @param string $endpoint
     */
    protected static function endpoint($endpoint)
    {
        self::$endpoint = $endpoint;
    }

    /**
     * @param string $query
     * @param array  $variables
     */
    protected static function send($query, array $variables = [])
    {
        self::$query = ['query' => $query, 'variables' => $variables];
        self::getClient()->request(Request::METHOD_POST, self::$endpoint, [], [], [], json_encode(self::$query));
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
     * debugQuery
     */
    protected static function debugQuery()
    {
        print_r(self::$query);
    }
}
