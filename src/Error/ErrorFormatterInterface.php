<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Error;

use GraphQL\Error\Error;

/**
 * Formatter is responsible for converting instances of GraphQL\Error\Error to an array.
 */
interface ErrorFormatterInterface
{
    /**
     * Standard GraphQL error formatter. Converts any exception to array
     * conforming to GraphQL spec.
     *
     * This method only exposes exception message when exception implements ClientAware interface
     * (or when debug flags are passed).
     *
     * For a list of available debug flags see GraphQL\Error\Debug constants.
     *
     * @param Error    $error
     * @param bool|int $debug
     *
     * @return array
     */
    public function format(Error $error, $debug = false): array;
}
