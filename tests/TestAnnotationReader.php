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
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\PhpFileCache;

/**
 * Create Annotation reader with cache to use in tests
 */
class TestAnnotationReader
{
    /**
     * @return Reader
     */
    public static function create(): Reader
    {
        $cache = new PhpFileCache(sys_get_temp_dir());

        return new CachedReader(new AnnotationReader(), $cache);
    }
}
