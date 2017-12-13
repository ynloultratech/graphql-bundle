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

use Doctrine\Common\Annotations\Reader;
use GraphQL\Type\Definition\EnumType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Form\Type\GraphQLType;
use Ynlo\GraphQLBundle\Type\Types;

/**
 * Class GraphQLTypeGuesser
 */
class GraphQLEnumTypeGuesser implements FormTypeGuesserInterface
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * ConnectionDefinitionBuilder constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        $refClass = new \ReflectionClass($class);
        if ($refClass->hasProperty($property)) {
            /** @var Annotation\Field $annotation */
            $annotation = $this->reader->getPropertyAnnotation($refClass->getProperty($property), Annotation\Field::class);

            if ($annotation && $annotation->type && $type = Types::get($annotation->type)) {
                if ($type instanceof EnumType) {
                    return new TypeGuess(GraphQLType::class, ['graphql_type' => $annotation->type], Guess::VERY_HIGH_CONFIDENCE);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function guessRequired($class, $property)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function guessMaxLength($class, $property)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function guessPattern($class, $property)
    {
    }
}
