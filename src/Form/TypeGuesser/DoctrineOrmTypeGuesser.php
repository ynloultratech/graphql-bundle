<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Form\TypeGuesser;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser as BaseDoctrineOrmTypeGuesser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Ynlo\GraphQLBundle\Form\Type\GraphQLType;
use Ynlo\GraphQLBundle\Type\Types;

/**
 * DoctrineOrmTypeGuesser
 */
class DoctrineOrmTypeGuesser extends BaseDoctrineOrmTypeGuesser
{
    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        if (!$ret = $this->getMetadata($class)) {
            return new TypeGuess(TextType::class, [], Guess::LOW_CONFIDENCE);
        }

        /** @var ClassMetadata $metadata */
        list($metadata, $name) = $ret;

        if ($metadata->hasAssociation($property)) {
            $multiple = $metadata->isCollectionValuedAssociation($property);
            $mapping = $metadata->getAssociationMapping($property);

            return new TypeGuess(
                EntityType::class,
                [
                    'em' => $name,
                    'class' => $mapping['targetEntity'],
                    'multiple' => $multiple,
                ],
                Guess::HIGH_CONFIDENCE
            );
        }

        switch ($metadata->getTypeOfField($property)) {
            case Type::TARRAY:
                return new TypeGuess(CollectionType::class, [], Guess::MEDIUM_CONFIDENCE);
            case Type::BOOLEAN:
                return new TypeGuess(CheckboxType::class, [], Guess::HIGH_CONFIDENCE);
            case Type::DATETIME:
            case Type::DATETIMETZ:
            case 'vardatetime':
                return new TypeGuess(GraphQLType::class, ['graphql_type' => Types::DATETIME], Guess::HIGH_CONFIDENCE);
            case 'dateinterval':
                return new TypeGuess(DateIntervalType::class, [], Guess::HIGH_CONFIDENCE);
            case Type::DATE:
                return new TypeGuess(TextType::class, [], Guess::HIGH_CONFIDENCE);
            case Type::TIME:
                return new TypeGuess(TimeType::class, [], Guess::HIGH_CONFIDENCE);
            case Type::DECIMAL:
            case Type::FLOAT:
                return new TypeGuess(NumberType::class, [], Guess::MEDIUM_CONFIDENCE);
            case Type::INTEGER:
            case Type::BIGINT:
            case Type::SMALLINT:
                return new TypeGuess(IntegerType::class, [], Guess::MEDIUM_CONFIDENCE);
            case Type::STRING:
                return new TypeGuess(TextType::class, [], Guess::MEDIUM_CONFIDENCE);
            case Type::SIMPLE_ARRAY:
            case Type::JSON_ARRAY:
                return new TypeGuess(GraphQLType::class, ['graphql_type' => Types::listOf(Types::STRING) ], Guess::MEDIUM_CONFIDENCE);
            case Type::TEXT:
                return new TypeGuess(TextareaType::class, [], Guess::MEDIUM_CONFIDENCE);
            default:
                return new TypeGuess(TextType::class, [], Guess::LOW_CONFIDENCE);
        }
    }
}
