<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Type\Registry;

use GraphQL\Type\Definition\Type;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ynlo\GraphQLBundle\Definition\EnumDefinition;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Definition\UnionDefinition;
use Ynlo\GraphQLBundle\Type\Definition\EndpointAwareInterface;
use Ynlo\GraphQLBundle\Type\Definition\EnumDefinitionType;
use Ynlo\GraphQLBundle\Type\Definition\InputObjectDefinitionType;
use Ynlo\GraphQLBundle\Type\Definition\InterfaceDefinitionType;
use Ynlo\GraphQLBundle\Type\Definition\ObjectDefinitionType;
use Ynlo\GraphQLBundle\Type\Definition\UnionDefinitionType;
use Ynlo\GraphQLBundle\Type\Types;

class TypeRegistry
{
    /**
     * @var Endpoint
     */
    protected static $endpoint;

    /**
     * @var ContainerInterface
     */
    protected static $container;

    /**
     * @var Type[]
     */
    protected static $types = [];

    /**
     * @var array
     */
    protected static $typesMap = [];

    public static function setUp(ContainerInterface $container, Endpoint $endpoint)
    {
        self::$container = $container;
        self::$endpoint = $endpoint;
    }

    /**
     * Clear all registered instantiated types
     */
    public static function clear()
    {
        self::$types = [];
    }

    /**
     * @param string $name
     *
     * @return Type
     *
     * @throws \UnexpectedValueException if not valid type found
     */
    public static function get($name): Type
    {
        //internal type
        if ($internalType = self::getInternalType($name)) {
            return $internalType;
        }

        //convert FQCN into type,
        //allowing the use of FQCN for GraphQL scalar types
        if ($name && (class_exists($name) || interface_exists($name))) {
            if (\in_array($name, self::$typesMap, true)) {
                $name = array_flip(self::$typesMap)[$name];
            } elseif (self::$endpoint->hasTypeForClass($name)) {
                $name = self::$endpoint->getTypeForClass($name);
            }
        }

        if (!self::has($name)) {
            self::create($name);
        }

        if (self::has($name)) {
            return self::$types[$name];
        }

        throw new InvalidTypeException($name);
    }

    public static function create(string $name): void
    {
        $type = null;

        //create using auto-loaded types
        if (array_key_exists($name, self::$typesMap)) {
            $class = self::$typesMap[$name];

            /** @var Type $type */
            $type = new $class();
            if (self::$container && $type instanceof ContainerAwareInterface) {
                $type->setContainer(self::$container);
            }
            if (self::$endpoint && $type instanceof EndpointAwareInterface) {
                $type->setEndpoint(self::$endpoint);
            }
            self::set($name, $type);
        }

        //create using definition endpoint
        if (self::$endpoint && self::$endpoint->hasType($name)) {
            $definition = self::$endpoint->getType($name);

            if ($definition instanceof ObjectDefinition) {
                $type = new ObjectDefinitionType($definition);
            }

            if ($definition instanceof UnionDefinition) {
                $type = new UnionDefinitionType($definition);
            }

            if ($definition instanceof InputObjectDefinition) {
                $type = new InputObjectDefinitionType($definition);
            }

            if ($definition instanceof InterfaceDefinition) {
                $type = new InterfaceDefinitionType($definition);
            }

            if ($definition instanceof EnumDefinition) {
                $type = new EnumDefinitionType($definition);
            }

            if ($type instanceof ContainerAwareInterface) {
                $type->setContainer(self::$container);
            }

            if ($type instanceof EndpointAwareInterface) {
                $type->setEndpoint(self::$endpoint);
            }

            if (null !== $type) {
                self::set($name, $type);
            }
        }
    }

    /**
     * @return Type[]
     */
    public static function all()
    {
        return self::$types;
    }

    /**
     * @param string $name
     * @param Type   $type
     */
    public static function set($name, Type $type)
    {
        self::$types[$name] = $type;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function has($name)
    {
        //convert FQN into type,
        //allowing the use of FQN for GraphQL scalar types
        if ($name && (class_exists($name) || interface_exists($name))) {
            if (\in_array($name, self::$typesMap, true)) {
                $name = array_flip(self::$typesMap)[$name];
            }
        }

        if (!array_key_exists($name, self::$types) && array_key_exists($name, self::$typesMap)) {
            static::create($name);
        }

        return array_key_exists($name, self::$types);
    }

    /**
     * Add type mapping information to use with the autoloader
     *
     * @param string $name
     * @param string $class
     */
    public static function addTypeMapping($name, $class)
    {
        self::$typesMap[$name] = $class;
    }

    /**
     * @param array $map
     */
    public static function setTypeMapping($map)
    {
        self::$typesMap = $map;
    }

    /**
     * @return array
     */
    public static function getTypeMapp()
    {
        return self::$typesMap;
    }

    /**
     * @param string $name
     *
     * @return Type
     */
    private static function getInternalType($name): ?Type
    {
        switch ($name) {
            case Types::STRING:
            case 'string':
                return Type::string();
            case Types::BOOLEAN:
            case 'boolean':
            case 'bool':
                return Type::boolean();
            case Types::INT:
            case 'int':
            case 'integer':
                return Type::int();
            case Types::FLOAT:
            case 'float':
            case 'decimal':
            case 'double':
                return Type::float();
            case Types::ID:
            case 'id':
            case 'ID':
                return Type::id();
        }

        return null;
    }
}
