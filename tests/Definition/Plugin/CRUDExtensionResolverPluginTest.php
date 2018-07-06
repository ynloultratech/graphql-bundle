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
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\HasExtensionsInterface;
use Ynlo\GraphQLBundle\Definition\Plugin\CRUDExtensionResolverPlugin;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\Post;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Extension\HasAuthorExtension;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model\HasAuthorInterface;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class CRUDExtensionResolverPluginTest extends TestCase
{
    public function testConfigure()
    {
        $plugin = new CRUDExtensionResolverPlugin();
        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [ObjectType::class]);

        /** @var HasExtensionsInterface $post */
        $post = $endpoint->getType(Post::class);
        $definition = $endpoint->getType(HasAuthorInterface::class);

        self::assertEmpty($post->getExtensions());
        $plugin->configure($definition, $endpoint, []);

        self::assertEquals(HasAuthorExtension::class, $post->getExtensions()[HasAuthorExtension::class]->getClass());
    }

    public function testConfigureRealInterface()
    {
        $plugin = new CRUDExtensionResolverPlugin();
        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(Post::class, $endpoint, [ObjectType::class]);

        /** @var HasExtensionsInterface|DefinitionInterface $post */
        $post = $endpoint->getType(Post::class);

        self::assertEmpty($post->getExtensions());
        $plugin->configure($post, $endpoint, []);

        self::assertEquals(HasAuthorExtension::class, $post->getExtensions()[HasAuthorExtension::class]->getClass());
    }
}
