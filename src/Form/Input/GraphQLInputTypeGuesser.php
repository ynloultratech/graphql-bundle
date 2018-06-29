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

use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Form\Type\GraphQLType;
use Ynlo\GraphQLBundle\Form\Type\IDType;
use Ynlo\GraphQLBundle\Type\Types;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * Guess correct GraphQL input type for internal GraphQL field types
 */
class GraphQLInputTypeGuesser implements InputFieldTypeGuesser
{
    /**
     * @inheritDoc
     */
    public function guessType(FieldDefinition $field, string $type, array $options): ?TypeGuess
    {
        switch ($type) {
            case GraphQLType::class:
                return new TypeGuess(
                    TypeUtil::normalize($options['graphql_type']),
                    [
                        'list' => TypeUtil::isTypeList($options['graphql_type']),
                        'required' => TypeUtil::isTypeNonNull($options['graphql_type']),
                    ],
                    Guess::VERY_HIGH_CONFIDENCE
                );

            case IDType::class:
                return new TypeGuess(
                    Types::ID,
                    [
                        'list' => $options['multiple'] ?? false,
                    ],
                    Guess::VERY_HIGH_CONFIDENCE
                );

        }

        return null;
    }
}
