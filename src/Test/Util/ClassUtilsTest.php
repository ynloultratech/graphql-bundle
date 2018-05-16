<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Test\Util;

use PHPUnit\Framework\TestCase;
use Ynlo\GraphQLBundle\Util\ClassUtils;

class ClassUtilsTest extends TestCase
{

    public function testRelatedBundleNamespace()
    {
        self::assertEquals('AppBundle', ClassUtils::relatedBundleNamespace('AppBundle\Entity\User'));
        self::assertEquals('AppBundle', ClassUtils::relatedBundleNamespace('AppBundle\User'));
        self::assertEquals('Ynlo\GraphQLBundle\Demo\AppBundle', ClassUtils::relatedBundleNamespace('Ynlo\GraphQLBundle\Demo\AppBundle\Test'));
        self::assertEquals('AppBundle', ClassUtils::relatedBundleNamespace('AppBundle\Test\HelloBundleName'));
        self::assertEquals('App', ClassUtils::relatedBundleNamespace('App\Test\HelloBundleName'));
    }
}
