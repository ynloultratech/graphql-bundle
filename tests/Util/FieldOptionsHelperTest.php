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
use Ynlo\GraphQLBundle\Util\FieldOptionsHelper;

class FieldOptionsHelperTest extends TestCase
{

    public function testHelper()
    {
        $options = ['*', 'name,title' => true, 'body,date' => false, 'tags' => [], 'description,notes'];

        $normalizedOptions = FieldOptionsHelper::normalize($options);
        self::assertCount(8, $normalizedOptions);
        self::assertEquals(true, $normalizedOptions['*']);
        self::assertEquals(true, $normalizedOptions['name']);
        self::assertEquals(true, $normalizedOptions['title']);
        self::assertEquals(false, $normalizedOptions['body']);
        self::assertEquals(false, $normalizedOptions['date']);
        self::assertEquals(true, $normalizedOptions['description']);
        self::assertEquals(true, $normalizedOptions['notes']);
        self::assertEquals([], $normalizedOptions['tags']);

        self::assertTrue(FieldOptionsHelper::isEnabled($options, 'name'));
        self::assertTrue(FieldOptionsHelper::isEnabled($options, 'lastName'));
        self::assertTrue(FieldOptionsHelper::isEnabled($options, 'description'));
        self::assertFalse(FieldOptionsHelper::isEnabled($options, 'body'));

        self::assertEquals([], FieldOptionsHelper::getConfig($options, 'tags'));
        self::assertEquals(true, FieldOptionsHelper::getConfig($options, 'name'));
        self::assertEquals(false, FieldOptionsHelper::getConfig($options, 'body'));
        self::assertEquals(true, FieldOptionsHelper::getConfig($options, 'description'));
        self::assertEquals(true, FieldOptionsHelper::getConfig($options, 'lastName', true));
    }
}
