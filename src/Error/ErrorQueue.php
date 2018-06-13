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

/**
 * Throw errors in a queue in order to display later
 * alongside the data in the response. Works similar to Symfony FlashBag message container.
 *
 * For example, a client make a mutation and the operation
 * is made partially, the server must need return the modified data to the client
 * but is needed too inform of any error happened to display why the operation has been made partially.
 *
 * It’s like having a talk with a real human:
 * "Hey Johny, here are all you asked me to save. I did everything except that task; I went to save it up, but I don't permission to save it".
 */
class ErrorQueue
{
    /**
     * @var array|\Throwable[]
     */
    private static $errors = [];

    /**
     * @param \Throwable $error
     */
    public static function throw(\Throwable $error): void
    {
        try {
            throw $error;
        } catch (\Throwable $e) {
            self::$errors[$error->getCode()] = $e;
        }
    }

    /**
     * Gets error for a given type.
     *
     * @param string|int $code
     *
     * @return null|\Throwable
     */
    public static function peek($code): ?\Throwable
    {
        return self::$errors[$code] ?? null;
    }

    /**
     * Gets all errors in queue.
     *
     * @return array
     */
    public static function peekAll(): array
    {
        return self::$errors;
    }

    /**
     * Remove given error code from the queue
     *
     * @param string|int $code
     */
    public static function remove($code): void
    {
        unset(self::$errors[$code]);
    }

    /**
     * Has error for a given code?
     *
     * @param string|int $code
     *
     * @return bool
     */
    public static function has($code): bool
    {
        return isset(self::$errors[$code]);
    }

    /**
     * Gets and clears errors from the queue.
     *
     * @return array|\Throwable[]
     */
    public static function all(): array
    {
        $errors = self::$errors;
        self::$errors = [];

        return $errors;
    }
}
