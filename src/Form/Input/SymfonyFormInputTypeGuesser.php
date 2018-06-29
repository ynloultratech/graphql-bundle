<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Form\Input;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Type\Types;

/**
 * Guess the correctness GraphQL type for build-in symfony form types
 */
class SymfonyFormInputTypeGuesser implements InputFieldTypeGuesser
{
    /**
     * @inheritDoc
     */
    public function guessType(FieldDefinition $field, string $type, array $options = []): ?TypeGuess
    {
        $guessOptions['list'] = $options['multiple'] ?? false;

        switch ($type) {
            case TextType::class:
            case PasswordType::class:
            case TextareaType::class:
            case EmailType::class:
                return new TypeGuess(Types::STRING, $guessOptions, Guess::HIGH_CONFIDENCE);
            case CheckboxType::class:
                return new TypeGuess(Types::BOOLEAN, $guessOptions, Guess::HIGH_CONFIDENCE);
            case IntegerType::class:
                return new TypeGuess(Types::INT, $guessOptions, Guess::HIGH_CONFIDENCE);
            case NumberType::class:
            case MoneyType::class:
                return new TypeGuess(Types::FLOAT, $guessOptions, Guess::HIGH_CONFIDENCE);
        }

        return null;
    }
}
