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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Form\Input\SymfonyFormInputTypeGuesser;
use Ynlo\GraphQLBundle\Type\Types;

class SymfonyFormInputTypeGuesserTest extends TestCase
{
    public function testGuessType()
    {
        $guesser = new SymfonyFormInputTypeGuesser();

        self::assertTrue($guesser->guessType(new FieldDefinition(), TextType::class, ['multiple' => true])->getOptions()['list']);
        self::assertEquals(Types::STRING, $guesser->guessType(new FieldDefinition(), TextType::class, [])->getType());
        self::assertEquals(Types::STRING, $guesser->guessType(new FieldDefinition(), PasswordType::class, [])->getType());
        self::assertEquals(Types::STRING, $guesser->guessType(new FieldDefinition(), TextareaType::class, [])->getType());
        self::assertEquals(Types::STRING, $guesser->guessType(new FieldDefinition(), EmailType::class, [])->getType());
        self::assertEquals(Types::BOOLEAN, $guesser->guessType(new FieldDefinition(), CheckboxType::class, [])->getType());
        self::assertEquals(Types::INT, $guesser->guessType(new FieldDefinition(), IntegerType::class, [])->getType());
        self::assertEquals(Types::FLOAT, $guesser->guessType(new FieldDefinition(), NumberType::class, [])->getType());
        self::assertEquals(Types::FLOAT, $guesser->guessType(new FieldDefinition(), MoneyType::class, [])->getType());

        self::assertNull($guesser->guessType(new FieldDefinition(), 'SomeType', []));
    }
}
