<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Type;

use GraphQL\Type\Definition\Type;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * Class Types
 */
class Types
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

    /**
     * @param ContainerInterface $container
     * @param Endpoint           $endpoint
     */
    public static function setUp(ContainerInterface $container, Endpoint $endpoint)
    {
        self::$container = $container;
        self::$endpoint = $endpoint;
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

        if (!self::has($name)) {
            self::create($name);
        }

        if (self::has($name)) {
            return self::$types[$name];
        }

        throw new \UnexpectedValueException(sprintf('Can`t find a valid type for given type "%s"', $name));
    }

    /**
     * @param string $name
     */
    public static function create($name)
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
                $type = new class($definition) extends AbstractObjectType
                {

                };
            } elseif ($definition instanceof InputObjectDefinition) {
                $type = new class($definition) extends AbstractInputObjectType
                {

                };
            } elseif ($definition instanceof InterfaceDefinition) {
                $type = new class(self::$endpoint, $definition) extends AbstractInterfaceType
                {

                };
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
     * @param string $name
     *
     * @return Type
     */
    private static function getInternalType($name): ?Type
    {
        switch ($name) {
            case Type::STRING:
            case 'string':
                return Type::string();
            case Type::BOOLEAN:
            case 'boolean':
            case 'bool':
                return Type::boolean();
            case Type::INT:
            case 'int':
            case 'integer':
                return Type::int();
            case Type::FLOAT:
            case 'float':
            case 'decimal':
            case 'double':
                return Type::float();
            case Type::ID:
            case 'id':
            case 'ID':
                return Type::id();
        }

        return null;
    }
}
