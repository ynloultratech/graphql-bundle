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
 * Handler is useful for error filtering and logging.
 */
interface ErrorHandlerInterface
{
    /**
     * @param Error[]|array           $errors
     * @param ErrorFormatterInterface $errorFormatter
     * @param bool|int                $debug
     *
     * @return array
     */
    public function handle(array $errors, ErrorFormatterInterface $errorFormatter, $debug = false): array;
}
