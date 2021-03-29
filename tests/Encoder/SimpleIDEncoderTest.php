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

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Encoder\SimpleIDEncoder;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class SimpleIDEncoderTest extends MockeryTestCase
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var DefinitionRegistry
     */
    protected $definitionRegistry;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [ObjectType::class]);
        $this->definitionRegistry = \Mockery::mock(DefinitionRegistry::class);
        $this->definitionRegistry->allows('getEndpoint')->andReturn($endpoint);

        $em = \Mockery::mock(EntityManagerInterface::class);
        $em->allows('getReference')->andReturnUsing(
            function ($class, $id) {
                return new $class($id);
            }
        );

        $this->registry = \Mockery::mock(Registry::class);
        $this->registry->allows('getManager')->andReturn($em);
    }

    public function testDecoder()
    {
        $encoder = new SimpleIDEncoder($this->definitionRegistry, $this->registry);

        $encodedId = $encoder->encode(new Post(1));

        self::assertEquals('Post:1', $encodedId);

        $node = $encoder->decode($encodedId);
        self::assertNotNull($node);
        self::assertEquals(Post::class, \get_class($node));
        self::assertEquals(1, $node->getId());
    }
}
