<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Form\DataTransformer;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Encoder\SimpleIDEncoder;
use Ynlo\GraphQLBundle\Form\DataTransformer\IDToNodeTransformer;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;
use Ynlo\GraphQLBundle\Util\IDEncoder;

class IDToNodeTransformerTest extends MockeryTestCase
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
    protected function setUp()
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
        IDEncoder::setup(new SimpleIDEncoder($this->definitionRegistry, $this->registry));
    }

    public function testTransform()
    {
        $transformer = new IDToNodeTransformer();

        $data = $transformer->transform(null);
        self::assertNull($data);

        $data = $transformer->transform(new Post(1));
        self::assertEquals('Post:1', $data);

        $data = $transformer->transform([new Post(1), new Post(2)]);
        self::assertEquals(['Post:1', 'Post:2'], $data);
    }

    public function testReverseTransform()
    {
        $transformer = new IDToNodeTransformer();

        $data = $transformer->reverseTransform(null);
        self::assertNull($data);

        $data = $transformer->reverseTransform('Post:1');
        self::assertEquals(1, $data->getId());

        $data = $transformer->reverseTransform(['Post:1', 'Post:2']);
        self::assertEquals(1, $data[0]->getId());
        self::assertEquals(2, $data[1]->getId());
    }

    public function testReverseTransformFindBy()
    {
        $repo = \Mockery::mock(ObjectRepository::class);
        $repo->allows('findOneBy')->withArgs([['name' => 'john']])->andReturn(new  Post(1));
        $transformer = new IDToNodeTransformer($repo, 'name');

        $data = $transformer->reverseTransform('john');
        self::assertEquals(1, $data->getId());
    }

    public function testReverseTransformExpection()
    {
        $transformer = new IDToNodeTransformer();
        self::expectException(TransformationFailedException::class);
        $transformer->reverseTransform('User:1');
    }
}
