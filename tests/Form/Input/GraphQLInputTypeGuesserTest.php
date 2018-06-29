<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Form\Input;

use Doctrine\DBAL\Types\TextType;
use PHPUnit\Framework\TestCase;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Form\Input\GraphQLInputTypeGuesser;
use Ynlo\GraphQLBundle\Form\Type\GraphQLType;
use Ynlo\GraphQLBundle\Form\Type\IDType;
use Ynlo\GraphQLBundle\Type\Types;

class GraphQLInputTypeGuesserTest extends TestCase
{

    public function testGuessType()
    {
        $guesser = new GraphQLInputTypeGuesser();

        self::assertEquals(
            Types::STRING,
            $guesser->guessType(
                new FieldDefinition(),
                GraphQLType::class,
                [
                    'graphql_type' => 'string',
                ]
            )->getType()
        );

        self::assertTrue(
            $guesser->guessType(
                new FieldDefinition(),
                GraphQLType::class,
                [
                    'graphql_type' => '[string]',
                ]
            )->getOptions()['list']
        );

        self::assertTrue(
            $guesser->guessType(
                new FieldDefinition(),
                GraphQLType::class,
                [
                    'graphql_type' => 'string!',
                ]
            )->getOptions()['required']
        );

        self::assertEquals(Types::ID, $guesser->guessType(new FieldDefinition(), IDType::class, [])->getType());
        self::assertFalse($guesser->guessType(new FieldDefinition(), IDType::class, [])->getOptions()['list'] ?? false);
        self::assertTrue($guesser->guessType(new FieldDefinition(), IDType::class, ['multiple' => true])->getOptions()['list']);

        self::assertNull($guesser->guessType(new FieldDefinition(), TextType::class, ['multiple' => true]));
    }
}
