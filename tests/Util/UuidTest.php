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
use Ynlo\GraphQLBundle\Util\Uuid;

class UuidTest extends TestCase
{

    public function testcreateFromData()
    {
        $data = ['message' => 'hello'];
        $uuid = Uuid::createFromData($data);
        $uuid2 = Uuid::createFromData($data);
        self::assertEquals($uuid, $uuid2);
        self::assertEquals('822B802D-B7C5-A74C-4A84-18F70A90', $uuid);
    }
}
