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

use Doctrine\Common\Util\Inflector;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Request;
use Ynlo\GraphQLBundle\Model\ID;

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
     * @param string     $name
     * @param array      $parameters (optional)
     * @param array|null $expected
     */
    protected static function query($name, array $parameters, array $expected = null)
    {
        if (null === $expected) {
            $expected = $parameters;
            $parameters = [];
        }
        self::graphqlQuery('query', $name, $parameters, $expected);
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param array  $expected
     */
    protected static function mutation($name, array $parameters = [], array $expected = [])
    {
        self::graphqlQuery('mutation', $name, $parameters, $expected);
    }


    /**
     * @param string $nodeType
     * @param string $databaseId
     *
     * @return string
     */
    protected static function encodeID($nodeType, $databaseId)
    {
        return ID::encode($nodeType, $databaseId);
    }

    /**
     * debugQuery
     */
    protected static function debugQuery()
    {
        print_r(self::$query);
    }

    /**
     * @param string $type
     * @param string $name
     * @param array  $parameters
     * @param array  $expected
     */
    private static function graphqlQuery($type, $name, array $parameters = [], array $expected = [])
    {
        $flattenQuery = self::flattenQuery($name, $parameters, $expected);
        if (strpos($name, '.') !== false) {
            $name = Inflector::camelize(str_replace('.', '_', $name));
        }
        $query =
            <<<GrahpQL
$type $name{
  $flattenQuery
}
GrahpQL;

        $body = ['query' => $query];
        self::$query = $query;
        self::getClient()->request(Request::METHOD_POST, self::$endpoint, [], [], [], json_encode($body));
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param array  $expected
     *
     * @return string
     */
    private static function flattenQuery($name, array $parameters = [], array $expected = [])
    {
        $expectedStr = self::flattenExpectation($expected);

        //namespaced query
        if (strpos($name, '.') !== false) {
            $namespace = explode('.', $name);

            $name = array_reverse($namespace)[0];
            array_pop($namespace);
            $query = '{child}';
            foreach ($namespace as $path) {
                $content = <<<GrahpQL
$path {
    {child}
}
GrahpQL;
                $query = str_replace('{child}', $content, $query);
            }

            return str_replace('{child}', self::flattenQuery($name, $parameters, $expected), $query);
        }


        $paramsStr = null;
        if ($parameters) {
            $paramsStr = self::flattenParameters($parameters);
        }

        return <<<GrahpQL
  $name$paramsStr{
    $expectedStr
  }
GrahpQL;
    }

    /**
     * @param array $parameters
     * @param array $wrappers
     *
     * @return string
     */
    private static function flattenParameters(array $parameters, $wrappers = ['(', ')'])
    {
        foreach ($parameters as $key => &$value) {
            if (\is_string($value)) {
                $value = "\"$value\"";
            }
            if (\is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            if (\is_array($value)) {
                if (array_key_exists(0, $value)) {
                    $value = json_encode($value);
                } else {
                    $value = self::flattenParameters($value, ['{', '}']);
                }
            }
            $value = "$key: $value";
        }
        unset($value);

        list($start, $end) = $wrappers;

        return "$start ".implode(', ', $parameters)." $end";
    }

    /**
     * @param array $expectation
     *
     * @return string
     */
    private static function flattenExpectation(array $expectation)
    {
        $expectNormalized = [];
        foreach ($expectation as $path => $value) {
            if (\is_array($value)) {
                if (isset($value[0], $value[1])
                    && is_array($value[0])
                    && is_array($value[1])
                ) {
                    $value = self::flattenQuery($path, $value[0], $value[1]);
                } else {
                    $value = $path." {\n\t\t".self::flattenExpectation($value)."\n\t}";
                }
            }
            $expectNormalized[] = $value;
        }

        return implode("\n\t", $expectNormalized);
    }
}
