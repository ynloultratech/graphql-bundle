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

namespace Ynlo\GraphQLBundle\Tests\Util;

use Ynlo\GraphQLBundle\Util\Inflector;
use PHPUnit\Framework\TestCase;

class InflectorTest extends TestCase
{
    public function testTableize()
    {
        self::assertEquals('model_name', Inflector::tableize('ModelName'));
    }

    public function testClassify()
    {
        self::assertEquals('ModelName', Inflector::classify('model_name'));
    }
}
