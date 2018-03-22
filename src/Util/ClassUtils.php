<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Util;

/**
 * ClassUtils
 */
class ClassUtils
{
    /**
     * Get the bundle namespace class to any class inside a bundle
     *
     * @param string $class
     *
     * @return string
     */
    public static function relatedBundleNamespace($class)
    {
        return preg_replace('~Bundle\\\\(?!.*Bundle\\\\)[\\\\\w+]+~', null, $class).'Bundle';
    }

    /**
     * Apply a naming convention based on namespace and path for given node
     *
     * e.g.
     * $namespace = AppBundle
     * $path = Form\Input
     * $node = User
     * $name = AddUser
     * $input = Input
     *
     * result: AppBundle\Form\Input\User\AddUserInput
     *
     * @param string      $namespace
     * @param string      $path
     * @param string      $node
     * @param string      $name
     * @param string|null $suffix
     *
     * @return string
     */
    public static function applyNamingConvention(string $namespace, string $path, ?string $node, string $name, ?string $suffix = null)
    {
        if (null === $node) {
            return sprintf('%s\%s\%s%s', $namespace, $path, ucfirst($name), $suffix);
        }

        return sprintf('%s\%s\%s\%s%s', $namespace, $path, $node, ucfirst($name), $suffix);
    }

    /**
     * Get default object name based in given class using naming convention
     *
     * @param string $class
     *
     * @return string
     */
    public static function getDefaultName(string $class): string
    {
        preg_match('/\w+$/', $class, $matches);

        return $matches[0] ?? '';
    }

    /**
     * Get object type using naming convention
     * if Query is placed under User\AllUsers namespace, then "User" is the object type
     *
     * Mutation\User\UpdateUser -> User
     * Query\User\Users -> User
     * Form\Input\User\AddUserInput -> User
     *
     * @param string $class
     *
     * @return string
     */
    public static function getNodeFromClass(string $class): string
    {
        preg_match('/(\w+)(\\\\w+)?\\\\(\w+)$/', $class, $matches);

        return $matches[1];
    }
}
