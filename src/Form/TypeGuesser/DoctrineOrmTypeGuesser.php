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

use Doctrine\DBAL\Types\Types as DBALTypes;
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
        [$metadata, $name] = $ret;

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
            case DBALTypes::ARRAY:
                return new TypeGuess(CollectionType::class, [], Guess::MEDIUM_CONFIDENCE);
            case DBALTypes::BOOLEAN:
                return new TypeGuess(CheckboxType::class, [], Guess::HIGH_CONFIDENCE);
            case DBALTypes::DATETIME_MUTABLE:
            case DBALTypes::DATETIMETZ_MUTABLE:
                return new TypeGuess(TextType::class, ['graphql_type' => Types::DATETIME], Guess::HIGH_CONFIDENCE);
            case 'dateinterval':
                return new TypeGuess(DateIntervalType::class, [], Guess::HIGH_CONFIDENCE);
            case DBALTypes::DATE_MUTABLE:
                return new TypeGuess(TextType::class, ['graphql_type' => Types::DATE], Guess::HIGH_CONFIDENCE);
            case DBALTypes::TIME_MUTABLE:
                return new TypeGuess(TextType::class, ['graphql_type' => Types::TIME], Guess::HIGH_CONFIDENCE);
            case DBALTypes::DECIMAL:
            case DBALTypes::FLOAT:
                return new TypeGuess(NumberType::class, [], Guess::MEDIUM_CONFIDENCE);
            case DBALTypes::INTEGER:
            case DBALTypes::BIGINT:
            case DBALTypes::SMALLINT:
                return new TypeGuess(IntegerType::class, [], Guess::MEDIUM_CONFIDENCE);
            case DBALTypes::STRING:
                return new TypeGuess(TextType::class, [], Guess::MEDIUM_CONFIDENCE);
            case DBALTypes::SIMPLE_ARRAY:
            case DBALTypes::JSON:
            case DBALTypes::JSON_ARRAY:
                return new TypeGuess(GraphQLType::class, ['graphql_type' => Types::listOf(Types::STRING)], Guess::MEDIUM_CONFIDENCE);
            case DBALTypes::TEXT:
                return new TypeGuess(TextareaType::class, [], Guess::MEDIUM_CONFIDENCE);
            default:
                return new TypeGuess(TextType::class, [], Guess::LOW_CONFIDENCE);
        }
    }
}
