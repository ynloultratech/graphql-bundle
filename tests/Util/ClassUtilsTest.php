<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Util;

use PHPUnit\Framework\TestCase;
use Ynlo\GraphQLBundle\Util\ClassUtils;

class ClassUtilsTest extends TestCase
{
    public function testRelatedBundleNamesapce()
    {
        self::assertEquals('App', ClassUtils::relatedBundleNamespace('App\Entity\Class'));
        self::assertEquals('SomeBundle', ClassUtils::relatedBundleNamespace('SomeBundle\Folder\Class'));
        self::assertEquals('Vendor\SomeBundle', ClassUtils::relatedBundleNamespace('Vendor\SomeBundle\Folder\Class'));
        self::assertEquals('VendorBundle\SomeBundle', ClassUtils::relatedBundleNamespace('VendorBundle\SomeBundle\Class'));
        self::assertEquals('VendorBundle\SomeBundle', ClassUtils::relatedBundleNamespace('VendorBundle\SomeBundle\SomeBundle'));
    }

    public function testApplyNamingConvention()
    {
        self::assertEquals(
            'SomeBundle\Folder\Node\NameQuery',
            ClassUtils::applyNamingConvention('SomeBundle', 'Folder', 'Node', 'name', 'Query')
        );

        self::assertEquals(
            'SomeBundle\Folder\Node\Name',
            ClassUtils::applyNamingConvention('SomeBundle', 'Folder', 'Node', 'name')
        );

        self::assertEquals(
            'SomeBundle\Folder\Name',
            ClassUtils::applyNamingConvention('SomeBundle', 'Folder', null, 'name')
        );
    }

    public function testGetDefaultName()
    {
        self::assertEquals(
            'NameQuery',
            ClassUtils::getDefaultName('SomeBundle\Folder\Node\NameQuery')
        );
    }

    public function testGetNodeFromClass()
    {
        self::assertEquals(
            'User',
            ClassUtils::getNodeFromClass('Mutation\User\UpdateUser')
        );

        self::assertEquals(
            'User',
            ClassUtils::getNodeFromClass('Query\User\Users')
        );

        self::assertEquals(
            'User',
            ClassUtils::getNodeFromClass('Form\Input\User\AddUserInput')
        );
    }
}
