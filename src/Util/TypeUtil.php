<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Util;

use Doctrine\Common\Util\ClassUtils as DoctrineClassUtils;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Ynlo\GraphQLBundle\Definition\ClassAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\PolymorphicDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\PolymorphicObjectInterface;
use Ynlo\GraphQLBundle\Type\Types;

/**
 * Util to work with GraphQL types
 */
final class TypeUtil
{
    /**
     * Resolve the object type for given object instance
     *
     * @param Endpoint $endpoint
     * @param mixed    $object
     *
     * @return null|string
     */
    public static function resolveObjectType(Endpoint $endpoint, $object): ?string
    {
        if ($object instanceof PolymorphicObjectInterface && $object->getConcreteType()) {
            return $object->getConcreteType();
        }

        $class = DoctrineClassUtils::getClass($object);
        if (!$endpoint->hasTypeForClass($class)) {
            return null;
        }

        $types = $endpoint->getTypesForClass($class);

        //if only one type for given object class return the type
        if (count($types) === 1) {
            return $types[0];
        }

        //in case of multiple types using polymorphic definitions
        foreach ($types as $type) {
            $definition = $endpoint->getType($type);
            if ($definition instanceof PolymorphicDefinitionInterface) {
                return self::resolveConcreteType($endpoint, $definition, $object);
            }
        }

        //as fallback use the first type in the list
        return $types[0];
    }

    /**
     * Resolve the object type for given object instance when object use polymorphic definitions
     *
     * @param Endpoint                       $endpoint
     * @param PolymorphicDefinitionInterface $definition
     * @param mixed                          $object
     *
     * @return null|string
     */
    public static function resolveConcreteType(Endpoint $endpoint, PolymorphicDefinitionInterface $definition, $object)
    {
        //if discriminator map is set is used to get the value type
        if ($map = $definition->getDiscriminatorMap()) {
            //get concrete type based on property
            if ($definition->getDiscriminatorProperty()) {
                $property = $definition->getDiscriminatorProperty();
                $accessor = new PropertyAccessor();
                $propValue = $accessor->getValue($object, $property);
                $resolvedType = $map[$propValue] ?? null;
            }

            //get concrete type based on class
            if (!$resolvedType) {
                $class = DoctrineClassUtils::getClass($object);
                $resolvedType = $map[$class] ?? null;
            }
        }

        //final solution in case of not mapping is guess type based on class
        if (!$resolvedType && $definition instanceof InterfaceDefinition) {
            foreach ($definition->getImplementors() as $implementor) {
                $implementorDef = $endpoint->getType($implementor);
                if ($implementorDef instanceof ClassAwareDefinitionInterface
                    && $implementorDef->getClass() === DoctrineClassUtils::getClass($object)) {
                    $resolvedType = $implementorDef->getName();
                }
            }
        }

        if ($endpoint->hasType($resolvedType)) {
            $resolvedTypeDefinition = $endpoint->getType($resolvedType);
            if ($resolvedTypeDefinition instanceof PolymorphicDefinitionInterface) {
                $resolvedType = self::resolveConcreteType($endpoint, $resolvedTypeDefinition, $object);
            }
        }

        return $resolvedType;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isTypeList($type): bool
    {
        return (bool) preg_match('/^\[([\\\\\w]*)!?\]!?$/', $type);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isTypeNonNullList($type): bool
    {
        return (bool) preg_match('/^\[([\\\\\w]*)!\]!?$/', $type);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isTypeNonNull($type): bool
    {
        return (bool) preg_match('/!$/', $type);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public static function normalize($type)
    {
        if (preg_match('/^\[?([\\\\\w]*)!?\]?!?$/', $type, $matches)) {
            $type = $matches[1];
        }

        switch ($type) {
            case 'bool':
            case 'boolean':
                $type = Types::BOOLEAN;
                break;
            case 'decimal':
            case 'float':
                $type = Types::FLOAT;
                break;
            case 'int':
            case 'integer':
                $type = Types::INT;
                break;
            case 'string':
                $type = Types::STRING;
                break;
            case 'id':
                $type = Types::ID;
                break;
            case 'datetime':
            case 'date_time':
                $type = Types::DATETIME;
                break;
            case 'date':
                $type = Types::DATE;
                break;
            case 'time':
                $type = Types::TIME;
                break;
            case 'any':
                $type = Types::ANY;
                break;
            case 'dynamic':
            case 'dynamicObject':
            case 'dynamic_object':
                $type = Types::DYNAMIC_OBJECT;
                break;
        }

        return Inflector::classify($type);
    }
}
