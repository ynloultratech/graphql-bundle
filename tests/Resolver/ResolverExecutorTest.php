<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Resolver;

use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Encoder\IDEncoderInterface;
use Ynlo\GraphQLBundle\Extension\ExtensionManager;
use Ynlo\GraphQLBundle\Model\AddNodePayload;
use Ynlo\GraphQLBundle\Model\DeleteNodePayload;
use Ynlo\GraphQLBundle\Model\Filter\NodeComparisonExpression;
use Ynlo\GraphQLBundle\Model\Filter\StringComparisonExpression;
use Ynlo\GraphQLBundle\Model\UpdateNodePayload;
use Ynlo\GraphQLBundle\Resolver\ContextBuilder;
use Ynlo\GraphQLBundle\Resolver\ResolverContext;
use Ynlo\GraphQLBundle\Resolver\ResolverExecutor;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Profile;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\User;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;
use Ynlo\GraphQLBundle\Type\NodeComparisonOperatorType;
use Ynlo\GraphQLBundle\Type\StringComparisonOperatorType;
use Ynlo\GraphQLBundle\Util\IDEncoder;

class ResolverExecutorTest extends MockeryTestCase
{
    public function testExecute()
    {
        $encoder = \Mockery::mock(IDEncoderInterface::class);
        $encoder->allows('decode')->with('Post:1')->andReturn(new Post(1));
        $encoder->allows('decode')->with('User:1')->andReturn(new User(1));
        $encoder->allows('decode')->with('User:2')->andReturn(new User(2));

        IDEncoder::setup($encoder);

        $container = \Mockery::mock(ContainerInterface::class);

        $extensions = [];

        $extensionManager = \Mockery::mock(ExtensionManager::class);
        $extensionManager->allows('getExtensions')->andReturn($extensions);

        $eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);

        $resolver = \Mockery::mock(CustomResolver::class);
        $resolver->expects('setContainer')->with($container);
        $resolver->expects('setContext')->withAnyArgs();
        $resolver->expects('setExtensions')->with($extensions);
        $resolver->expects('setEventDispatcher')->with($eventDispatcher);
        $resolver->expects('__invoke')->withArgs(
            function (ResolverContext $context, User $root, $args) {
                self::assertInstanceOf(User::class, $root);
                self::assertEquals('default', $context->getEndpoint()->getName());
                self::assertEquals('User', $context->getNode()->getName());
                self::assertEquals('allUsers', $context->getDefinition()->getName());
                self::assertEquals($root, $context->getRoot());
                self::assertEquals(0, $root->getId());
                self::assertEquals('John', $args['name']);
                self::assertEquals(1, $args['post']->getId());
                self::assertInstanceOf(NodeComparisonExpression::class, $args['nodes']);
                self::assertEquals(NodeComparisonOperatorType::IN, $args['nodes']->getOp());
                self::assertEquals(1, $args['nodes']->getNodes()[0]->getId());
                self::assertEquals(2, $args['nodes']->getNodes()[1]->getId());
                self::assertEquals('john', $args['user'][0]->getUsername());
                self::assertEquals('johny', $args['user'][0]->getProfile()->getNick());
                self::assertEquals('david', $args['user'][1]->getUsername());
                self::assertEquals('david01', $args['user'][1]->getProfile()->getNick());
                self::assertEquals(StringComparisonOperatorType::CONTAINS, $args['where']['name']->getOp());
                self::assertEquals('john', $args['where']['name']->getValue());

                return true;
            }
        );

        $resolverClass = \get_class($resolver);

        $container->allows('has')->with($resolverClass)->andReturn(true);
        $container->allows('get')->with($resolverClass)->andReturn($resolver);
        $container->allows('get')->with(ExtensionManager::class)->andReturn($extensionManager);
        $container->allows('get')->with(EventDispatcherInterface::class)->andReturn($eventDispatcher);

        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(AddNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(UpdateNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(DeleteNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(User::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(Profile::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(NodeComparisonExpression::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(StringComparisonExpression::class, $endpoint);

        $query = $endpoint->getQuery('allUsers');

        $name = new ArgumentDefinition();
        $name->setName('name');
        $name->setType('string');
        $query->addArgument($name);

        $post = new ArgumentDefinition();
        $post->setName('post');
        $post->setType('ID');
        $query->addArgument($post);

        $user = new ArgumentDefinition();
        $user->setName('user');
        $user->setType('User');
        $user->setList(true);
        $query->addArgument($user);

        $nodes = new ArgumentDefinition();
        $nodes->setName('nodes');
        $nodes->setType('NodeComparisonExpression');
        $query->addArgument($nodes);

        $whereObject = new ObjectDefinition();
        $whereObject->setName('Where');
        $endpoint->add($whereObject);

        $name = new FieldDefinition();
        $name->setName('name');
        $name->setType('StringComparisonExpression');
        $whereObject->addField($name);

        $where = new ArgumentDefinition();
        $where->setName('where');
        $where->setType('Where');
        $query->addArgument($where);

        $query->setResolver($resolverClass);
        $resolverExecutor = new ResolverExecutor($container, $query);

        $args = [
            'name' => 'John',
            'post' => 'Post:1',
            'user' => [
                [
                    'login' => 'john',
                    'profile' => [
                        'nick' => 'johny',
                    ],
                ],
                [
                    'login' => 'david',
                    'profile' => [
                        'nick' => 'david01',
                    ],
                ],
            ],
            'nodes' => [
                'op' => NodeComparisonOperatorType::IN,
                'nodes' => ['User:1', 'User:2'],
            ],
            'where' => [
                'name' => [
                    'op' => StringComparisonOperatorType::CONTAINS,
                    'value' => 'john',
                ],
            ],
        ];

        $context = ContextBuilder::create($endpoint)
                                 ->setDefinition($query)
                                 ->setArgs($args)
                                 ->build();

        $resolverInfo = \Mockery::mock(ResolveInfo::class);
        $resolverInfo->operation = new OperationDefinitionNode([]);
        $resolverExecutor(new User(0), $args, $context, $resolverInfo);
    }
}
