<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Encoder;

use Ynlo\GraphQLBundle\Encoder\Base64IDEncoder;
use PHPUnit\Framework\TestCase;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;

class Base64IDEncoderTest extends SimpleIDEncoderTest
{
    public function testDecoder()
    {
        $encoder = new Base64IDEncoder($this->definitionRegistry, $this->registry);

        $encodedId = $encoder->encode(new Post(1));

        self::assertEquals('UG9zdDox', $encodedId);

        $node = $encoder->decode($encodedId);
        self::assertNotNull($node);
        self::assertEquals(Post::class, \get_class($node));
        self::assertEquals(1, $node->getId());
    }
}
