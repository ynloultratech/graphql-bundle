<?php
/*
 * ******************************************************************************
 * This file is part of the GraphQL Bundle package.
 *
 * (c) YnloUltratech <support@ynloultratech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  *****************************************************************************
 */

namespace Ynlo\GraphQLBundle\Util;

use Doctrine\Inflector\InflectorFactory;

/**
 * Class Inflector
 *
 * @package Ynlo\GraphQLBundle\Util
 */
class Inflector
{
    /**
     * Convert word in to the format for a Doctrine table name. Converts 'ModelName' to 'model_name'
     *
     * @param string $word Word to tableize
     *
     * @return string $word  Tableized word
     */
    public static function tableize($word)
    {
        // BC with doctrine inflector ^1.0
        if (!class_exists('Doctrine\Inflector\InflectorFactory')) {
            return \Doctrine\Common\Inflector\Inflector::tableize($word);
        }

        return InflectorFactory::create()->build()->tableize($word);
    }

    /**
     * Convert a word in to the format for a Doctrine class name. Converts 'table_name' to 'TableName'
     *
     * @param string $word Word to classify
     *
     * @return string $word  Classified word
     */
    public static function classify($word)
    {
        // BC with doctrine inflector ^1.0
        if (!class_exists('Doctrine\Inflector\InflectorFactory')) {
            return \Doctrine\Common\Inflector\Inflector::classify($word);
        }

        return InflectorFactory::create()->build()->classify($word);
    }

    /**
     * Camelize a word. This uses the classify() method and turns the first character to lowercase
     *
     * @param string $word
     *
     * @return string $word
     */
    public static function camelize($word)
    {
        // BC with doctrine inflector ^1.0
        if (!class_exists('Doctrine\Inflector\InflectorFactory')) {
            return \Doctrine\Common\Inflector\Inflector::camelize($word);
        }

        return InflectorFactory::create()->build()->camelize($word);
    }

    /**
     * Return $word in plural form.
     *
     * @param string $word Word in singular
     *
     * @return string Word in plural
     */
    public static function pluralize($word)
    {
        // BC with doctrine inflector ^1.0
        if (!class_exists('Doctrine\Inflector\InflectorFactory')) {
            return \Doctrine\Common\Inflector\Inflector::pluralize($word);
        }

        return InflectorFactory::create()->build()->pluralize($word);
    }

    /**
     * Return $word in singular form.
     *
     * @param string $word Word in plural
     *
     * @return string Word in singular
     */
    public static function singularize($word)
    {
        // BC with doctrine inflector ^1.0
        if (!class_exists('Doctrine\Inflector\InflectorFactory')) {
            return \Doctrine\Common\Inflector\Inflector::singularize($word);
        }

        return InflectorFactory::create()->build()->singularize($word);
    }
}