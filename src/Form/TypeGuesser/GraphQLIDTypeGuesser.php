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
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Ynlo\GraphQLBundle\Form\Type\IDType;

/**
 * Class GraphQLTypeGuesser
 */
class GraphQLIDTypeGuesser implements FormTypeGuesserInterface
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
        if ('id' === $property) {
            return new TypeGuess(IDType::class, ['mapped' => false], Guess::VERY_HIGH_CONFIDENCE);
        }

        $refClass = new \ReflectionClass($class);
        if ($refClass->hasProperty($property)) {
            $annotations = $this->reader->getPropertyAnnotations($refClass->getProperty($property));

            foreach ($annotations as $annotation) {
                if ($annotation instanceof ManyToOne) {
                    return new TypeGuess(IDType::class, [], Guess::VERY_HIGH_CONFIDENCE);
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
