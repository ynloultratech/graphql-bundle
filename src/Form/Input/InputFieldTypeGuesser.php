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

use Symfony\Component\Form\Guess\TypeGuess;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;

/**
 * Guess the correctness GraphQL type for given form field.
 * Used to create input objects using symfony forms.
 */
interface InputFieldTypeGuesser
{
    /**
     * Returns a field guess with the GraphQL type of given form field.
     *
     * The guess allow the following options:
     *  - list: boolean
     *  - required: boolean
     *
     * @param FieldDefinition $field   GraphQL definition of the field to guess
     * @param string          $type    field form type
     * @param array           $options field form options
     *
     * @return null|TypeGuess
     */
    public function guessType(FieldDefinition $field, string $type, array $options): ?TypeGuess;
}
