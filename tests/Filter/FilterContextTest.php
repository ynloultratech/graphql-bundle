<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Filter;

use PHPUnit\Framework\TestCase;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Filter\FilterContext;

class FilterContextTest extends TestCase
{
    public function testContext()
    {
        $endpoint = new Endpoint('default');
        $node = new ObjectDefinition();
        $field = new FieldDefinition();
        $context = new FilterContext($endpoint, $node, $field);

        self::assertEquals($endpoint, $context->getEndpoint());
        self::assertEquals($node, $context->getNode());
        self::assertEquals($field, $context->getField());

        $context->setField(null);
        self::assertNull($context->getField());
    }
}
