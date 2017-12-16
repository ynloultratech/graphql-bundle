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
use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
use GraphQL\Type\Definition\EnumType;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Form\Type\GraphQLType;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * Class GraphQLTypeGuesser
 */
class GraphQLEnumTypeGuesser extends DoctrineOrmTypeGuesser
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var AbstractEnumType[]
     */
    protected $registeredEnumTypes = [];

    /**
     * GraphQLEnumTypeGuesser constructor.
     *
     * @param Reader          $reader
     * @param ManagerRegistry $registry
     * @param array           $registeredTypes
     */
    public function __construct(Reader $reader, ManagerRegistry $registry, array $registeredTypes)
    {
        parent::__construct($registry);

        $this->reader = $reader;

        foreach ($registeredTypes as $type => $details) {
            $this->registeredEnumTypes[$type] = $details['class'];
        }
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

            if ($annotation && $annotation->type && $type = TypeRegistry::get($annotation->type)) {
                if ($type instanceof EnumType) {
                    return new TypeGuess(GraphQLType::class, ['graphql_type' => $annotation->type], Guess::VERY_HIGH_CONFIDENCE);
                }
            }
        }

        //resolve types for DoctrineEnumBundle
        return $this->resolveDoctrineEnumBundleType($class, $property);
    }

    /**
     * @param string $class
     * @param string $property
     *
     * @return TypeGuess|null
     */
    protected function resolveDoctrineEnumBundleType($class, $property):?TypeGuess
    {
        if (!class_exists('\Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType')) {
            return null;
        }

        $classMetadata = $this->getMetadata($class);

        // If no metadata for this class - can't guess anything
        if (!$classMetadata) {
            return null;
        }

        /** @var \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata */
        list($metadata) = $classMetadata;
        $fieldType = $metadata->getTypeOfField($property);

        // This is not one of the registered ENUM types
        if (!isset($this->registeredEnumTypes[$fieldType])) {
            return null;
        }

        $registeredEnumTypeFQCN = $this->registeredEnumTypes[$fieldType];

        if (!is_subclass_of($registeredEnumTypeFQCN, '\Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType', true)) {
            return null;
        }

        // Get the choices from the fully qualified class name
        $parameters = [
            'graphql_type' => TypeUtil::normalize($fieldType),
        ];

        return new TypeGuess(GraphQLType::class, $parameters, Guess::VERY_HIGH_CONFIDENCE);
    }
}
