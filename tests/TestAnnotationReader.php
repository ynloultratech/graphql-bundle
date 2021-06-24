<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\PhpFileCache;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Create Annotation reader with cache to use in tests
 */
class TestAnnotationReader
{
    private static $initialized = false;

    /**
     * @return Reader
     */
    public static function create(): Reader
    {
        // doctrine/cache ~1.0
        if (class_exists('Doctrine\Common\Cache\PhpFileCache')) {
            $cache = new PhpFileCache(sys_get_temp_dir());
            if (!self::$initialized) {
                $cache->deleteAll();
                self::$initialized = true;
            }

            return new CachedReader(new AnnotationReader(), $cache);
        }

        // doctrine/cache ~2.0
        return new PsrCachedReader(new AnnotationReader(), new ArrayAdapter());
    }
}
