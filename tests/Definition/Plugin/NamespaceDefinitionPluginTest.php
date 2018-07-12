<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Definition\Plugin;

use PHPUnit\Framework\TestCase;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Plugin\NamespaceDefinitionPlugin;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\AddNodePayload;
use Ynlo\GraphQLBundle\Model\DeleteNodePayload;
use Ynlo\GraphQLBundle\Model\OrderBy;
use Ynlo\GraphQLBundle\Model\PageInfo;
use Ynlo\GraphQLBundle\Model\UpdateNodePayload;
use Ynlo\GraphQLBundle\Resolver\EmptyObjectResolver;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\PostComment;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\User;
use Ynlo\GraphQLBundle\Tests\Fixtures\BillingBundle\Entity\Invoice;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class NamespaceDefinitionPluginTest extends TestCase
{

    public function testConfigure()
    {
        $endpoint = new Endpoint('default');

        $plugin = new NamespaceDefinitionPlugin(
            [
                'bundles' => [
                    'enabled' => true,
                    'aliases' => [
                        'BillingBundle' => 'Accountant',
                    ],
                    'ignore' => [
                        'AppBundle',
                    ],
                ],
                'nodes' => [
                    'enabled' => true,
                    'aliases' => [
                        'Administrator' => 'User',
                    ],
                    'ignore' => [
                        'Customer',
                        Post::class,
                    ],
                ],
            ]
        );

        TestDefinitionHelper::loadAnnotationDefinitions(AddNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(UpdateNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(DeleteNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(Invoice::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(PostComment::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(User::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(OrderBy::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(PageInfo::class, $endpoint);

        //Node name
        /** @var ObjectDefinition $userDefinition */
        $userDefinition = $endpoint->getType('Administrator');
        $userDefinition->setNode('User');
        $plugin->configure($userDefinition, $endpoint, []);
        self::assertEquals(['bundle' => null, 'node' => 'User'], $userDefinition->getMeta('namespace'));

        //Node alias
        /** @var ObjectDefinition $userDefinition */
        $userDefinition = $endpoint->getType('Administrator');
        $userDefinition->setNode('Administrator');
        $plugin->configure($userDefinition, $endpoint, []);
        self::assertEquals(['bundle' => null, 'node' => 'User'], $userDefinition->getMeta('namespace'));

        //Bundle aliases
        /** @var ObjectDefinition $userDefinition */
        $definition = $endpoint->getType(Invoice::class);
        $plugin->configure($definition, $endpoint, []);
        self::assertEquals(['bundle' => 'Accountant', 'node' => 'Invoice'], $definition->getMeta('namespace'));
    }

    public function testConfigureEndpoint()
    {
        $endpoint = new Endpoint('default');

        $plugin = new NamespaceDefinitionPlugin(
            [
                'bundles' => [
                    'enabled' => true,
                    'aliases' => [
                        'BillingBundle' => 'Accountant',
                    ],
                    'ignore' => [
                        'AppBundle',
                    ],
                ],
                'nodes' => [
                    'enabled' => true,
                    'aliases' => [
                        'Administrator' => 'User',
                    ],
                    'ignore' => [
                        'Customer',
                        Post::class,
                    ],
                ],
            ]
        );

        TestDefinitionHelper::loadAnnotationDefinitions(AddNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(UpdateNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(DeleteNodePayload::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(Invoice::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(PostComment::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(User::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(OrderBy::class, $endpoint);
        TestDefinitionHelper::loadAnnotationDefinitions(PageInfo::class, $endpoint);

        self::assertTrue($endpoint->hasQuery('allUsers'));
        self::assertTrue($endpoint->hasQuery('allPosts'));
        self::assertTrue($endpoint->hasMutation('addUser'));
        self::assertTrue($endpoint->hasMutation('updateUser'));
        self::assertTrue($endpoint->hasMutation('deleteUser'));
        self::assertTrue($endpoint->hasQuery('allInvoices'));
        self::assertTrue($endpoint->hasMutation('addInvoice'));

        $plugin->configure($endpoint->getQuery('allUsers'), $endpoint, []);
        $plugin->configure($endpoint->getQuery('allPosts'), $endpoint, []);
        $plugin->configure($endpoint->getMutation('addUser'), $endpoint, []);
        $plugin->configure($endpoint->getMutation('updateUser'), $endpoint, []);
        $plugin->configure($endpoint->getMutation('deleteUser'), $endpoint, []);
        $plugin->configure($endpoint->getQuery('allInvoices'), $endpoint, []);
        $plugin->configure($endpoint->getMutation('addInvoice'), $endpoint, []);
        $plugin->configure($endpoint->getMutation('updateInvoice'), $endpoint, []);

        //to verify node using FQN
        $endpoint->getQuery('allUsers')->setMeta('namespace', ['node' => User::class]);
        $endpoint->getQuery('allPosts')->setMeta('namespace', ['namespace' => 'posts/articles']);

        $plugin->configureEndpoint($endpoint);

        self::assertFalse($endpoint->hasQuery('allInvoices'));
        self::assertFalse($endpoint->hasQuery('allUsers'));
        self::assertFalse($endpoint->hasQuery('allPosts'));
        self::assertFalse($endpoint->hasMutation('addUser'));
        self::assertFalse($endpoint->hasMutation('updateUser'));
        self::assertFalse($endpoint->hasMutation('deleteUser'));
        self::assertFalse($endpoint->hasMutation('addInvoice'));
        self::assertFalse($endpoint->hasMutation('updateInvoice'));

        self::assertTrue($endpoint->hasQuery('posts'));
        self::assertEquals(EmptyObjectResolver::class, $endpoint->getQuery('posts')->getResolver());
        self::assertEquals('PostsQuery', $endpoint->getQuery('posts')->getType());

        self::assertTrue($endpoint->getType('PostsQuery')->hasField('articles'));
        self::assertEquals('ArticlesQuery', $endpoint->getType('PostsQuery')->getField('articles')->getType());

        self::assertTrue($endpoint->getType('ArticlesQuery')->hasField('allPosts'));
        self::assertEquals('Post', $endpoint->getType('ArticlesQuery')->getField('allPosts')->getType());

        self::assertTrue($endpoint->hasQuery('users'));
        self::assertEquals(EmptyObjectResolver::class, $endpoint->getQuery('users')->getResolver());
        self::assertEquals('UserQuery', $endpoint->getQuery('users')->getType());

        self::assertTrue($endpoint->hasMutation('users'));
        self::assertEquals(EmptyObjectResolver::class, $endpoint->getMutation('users')->getResolver());
        self::assertEquals('UserMutation', $endpoint->getMutation('users')->getType());

        self::assertTrue($endpoint->hasQuery('accountant'));
        self::assertEquals(EmptyObjectResolver::class, $endpoint->getQuery('accountant')->getResolver());
        self::assertEquals('AccountantBundleQuery', $endpoint->getQuery('accountant')->getType());

        /** @var ObjectDefinition $invoiceBundleQuery */
        $invoiceBundleQuery = $endpoint->getType('AccountantBundleQuery');
        self::assertCount(1, $invoiceBundleQuery->getFields());
        self::assertTrue($invoiceBundleQuery->hasField('invoices'));
        self::assertEquals('InvoiceQuery', $invoiceBundleQuery->getField('invoices')->getType());

        /** @var ObjectDefinition $invoiceQuery */
        $invoiceQuery = $endpoint->getType('InvoiceQuery');
        self::assertCount(1, $invoiceQuery->getFields());
        self::assertTrue($invoiceQuery->hasField('all'));

        self::assertTrue($endpoint->hasMutation('accountant'));
        self::assertEquals(EmptyObjectResolver::class, $endpoint->getMutation('accountant')->getResolver());
        self::assertEquals('AccountantBundleMutation', $endpoint->getMutation('accountant')->getType());

        /** @var ObjectDefinition $invoiceBundleMutation */
        $invoiceBundleMutation = $endpoint->getType('AccountantBundleMutation');
        self::assertCount(1, $invoiceBundleMutation->getFields());
        self::assertTrue($invoiceBundleMutation->hasField('invoices'));
        self::assertEquals('InvoiceMutation', $invoiceBundleMutation->getField('invoices')->getType());

        /** @var ObjectDefinition $invoiceMutation */
        $invoiceMutation = $endpoint->getType('InvoiceMutation');
        self::assertCount(2, $invoiceMutation->getFields());
        self::assertTrue($invoiceMutation->hasField('add'));
        self::assertTrue($invoiceMutation->hasField('update'));
    }
}
