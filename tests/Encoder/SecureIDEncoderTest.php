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

use Ynlo\GraphQLBundle\Encoder\SecureIDEncoder;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;

class SecureIDEncoderTest extends SimpleIDEncoderTest
{
    public function testDecoder()
    {
        $encoder = new SecureIDEncoder($this->definitionRegistry, $this->registry, '5023085d2136975cb143a219eb8d0487');

        $encodedId = $encoder->encode(new Post(1));

        self::assertEquals('r9Em2e+2', $encodedId);

        $node = $encoder->decode($encodedId);
        self::assertNotNull($node);
        self::assertEquals(Post::class, \get_class($node));
        self::assertEquals(1, $node->getId());
    }
}
