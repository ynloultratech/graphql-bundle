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
 * This helper is used to work with some field options
 * in filters, orderByFields, searchFields etc. to get allowed fields
 * and configuration if have.
 *
 * Work with options like:
 *
 * [ "*", "title, date": false, "description"=> [...] ]
 */
class FieldOptionsHelper
{
    /**
     * @param array  $options
     * @param string $name
     * @param bool   $default
     *
     * @return bool
     */
    public static function isEnabled(array $options, string $name, $default = false): bool
    {
        $enabled = $default;
        $options = self::normalize($options);

        if (array_key_exists('*', $options)) {
            $enabled = $options['*'];
        }

        if (array_key_exists($name, $options)) {
            $enabled = (bool) $options[$name];
        }

        return $enabled;
    }

    /**
     * @param array  $options
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public static function getConfig(array $options, string $name, $default = null)
    {
        $options = self::normalize($options);
        if (array_key_exists($name, $options)) {
            return $options[$name];
        }

        return $default;
    }

    /**
     * @param array $options
     *
     * @return mixed
     */
    public static function normalize($options)
    {
        //normalize not indexed fields
        foreach ($options as $key => $value) {
            if (\is_int($key)) {
                if (!isset($options[$value])) {
                    $options[$value] = true;
                }
                unset($options[$key]);
            }
        }

        //normalize comma separated names
        foreach ($options as $fieldsNames => $option) {
            if (strpos($fieldsNames, ',') !== false) {
                $fieldNamesArray = explode(',', $fieldsNames);
                foreach ($fieldNamesArray as $name) {
                    $name = trim($name);
                    if (!isset($options[$name])) {
                        $options[$name] = $option;
                        unset($options[$fieldsNames]);
                    }
                }
            }
        }

        return $options;
    }
}
