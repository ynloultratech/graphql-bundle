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
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\Plugin\MutationFormResolverPlugin;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity\User;
use Ynlo\GraphQLBundle\Tests\TestDefinitionHelper;

class MutationFormResolverPluginTest extends TestCase
{
    public function testConfigure()
    {
        $endpoint = new Endpoint('default');
        TestDefinitionHelper::loadAnnotationDefinitions(User::class, $endpoint, [ObjectType::class]);

        $registry = new FormRegistry([], new ResolvedFormTypeFactory());
        $factory = new FormFactory($registry);

        $plugin = new MutationFormResolverPlugin($factory);

        $definition = new MutationDefinition();
        $definition->setName('addUser');
        $definition->setNode(User::class);

        $plugin->configure($definition, $endpoint, ['enabled' => true, 'type' => true]);

        self::assertTrue($endpoint->hasType('AddUserInput'));

        /** @var InputObjectDefinition $input */
        $input = $endpoint->getType('AddUserInput');
        self::assertInstanceOf(InputObjectDefinition::class, $input);
        self::assertCount(4, $input->getFields());

        self::assertEquals('String', $input->getField('clientMutationId')->getType());
        self::assertFalse($input->getField('clientMutationId')->isNonNull());

        self::assertEquals('String', $input->getField('username')->getType());
        self::assertTrue($input->getField('username')->isNonNull());

        self::assertEquals('String', $input->getField('password')->getType());
        self::assertTrue($input->getField('password')->isNonNull());

        self::assertEquals('AddUserProfileInput', $input->getField('profile')->getType());
        self::assertFalse($input->getField('profile')->isNonNull());

        /** @var InputObjectDefinition $profileInput */
        $profileInput = $endpoint->getType('AddUserProfileInput');
        self::assertInstanceOf(InputObjectDefinition::class, $profileInput);
        self::assertCount(9, $profileInput->getFields());

        self::assertEquals('String', $profileInput->getField('nick')->getType());
        self::assertFalse($profileInput->getField('nick')->isNonNull());

        self::assertEquals('String', $profileInput->getField('firstName')->getType());
        self::assertFalse($profileInput->getField('firstName')->isNonNull());

        self::assertEquals('String', $profileInput->getField('lastName')->getType());
        self::assertFalse($profileInput->getField('lastName')->isNonNull());

        self::assertEquals('Boolean', $profileInput->getField('single')->getType());
        self::assertFalse($profileInput->getField('single')->isNonNull());

        self::assertEquals('Float', $profileInput->getField('credits')->getType());
        self::assertFalse($profileInput->getField('credits')->isNonNull());

        self::assertEquals('Int', $profileInput->getField('reputation')->getType());
        self::assertFalse($profileInput->getField('reputation')->isNonNull());

        self::assertEquals('DateTime', $profileInput->getField('birthDate')->getType());
        self::assertFalse($profileInput->getField('birthDate')->isNonNull());

        self::assertEquals('String', $profileInput->getField('hobbies')->getType());
        self::assertTrue($profileInput->getField('hobbies')->isList());

        self::assertEquals('AddUserProfilePhotosInput', $profileInput->getField('photos')->getType());
        self::assertTrue($profileInput->getField('photos')->isList());

        /** @var InputObjectDefinition $photosInput */
        $photosInput = $endpoint->getType('AddUserProfilePhotosInput');
        self::assertInstanceOf(InputObjectDefinition::class, $photosInput);
        self::assertCount(2, $photosInput->getFields());

        self::assertEquals('String', $photosInput->getField('url')->getType());
        self::assertFalse($photosInput->getField('url')->isNonNull());

        self::assertEquals('String', $photosInput->getField('description')->getType());
        self::assertFalse($photosInput->getField('description')->isNonNull());
    }
}
