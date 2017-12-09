<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Registry;

use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;

/**
 * Class DefinitionManager
 */
class DefinitionManager
{
    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var ObjectDefinitionInterface[]
     */
    protected $types = [];

    /**
     * @var string[]
     */
    protected $typeMap = [];

    /**
     * @var InterfaceDefinition[]
     */
    protected $interfaces = [];

    /**
     * @var MutationDefinition[]
     */
    protected $mutations = [];

    /**
     * @var QueryDefinition[]
     */
    protected $queries = [];

    /**
     * DefinitionManager constructor.
     *
     * @param string $endpoint
     */
    public function __construct(string $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @return ObjectDefinitionInterface[]
     */
    public function allTypes(): array
    {
        return $this->types;
    }

    /**
     * @param string $type
     *
     * @return null|string
     */
    public function getClassForType(string $type):?string
    {
        if (isset($this->typeMap[$type])) {
            return $this->typeMap[$type];
        }

        return null;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function hasTypeForClass(string $class): bool
    {
        return in_array($class, $this->typeMap);
    }

    /**
     * @param string $class
     *
     * @return string
     *
     * @throws \UnexpectedValueException if the class does not have any valid type
     */
    public function getTypeForClass(string $class): string
    {
        $typeMap = array_flip($this->typeMap);
        if (isset($typeMap[$class])) {
            return $typeMap[$class];
        }

        $error = sprintf('Does not exist any valid type for class "%s"', $class);
        throw new \UnexpectedValueException($error);
    }

    /**
     * Helper method to avoid in runtime
     * recurring all types to get only interfaces
     *
     * @return InterfaceDefinition[]
     */
    public function allInterfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * @return array|MutationDefinition[]
     */
    public function allMutations(): array
    {
        return $this->mutations;
    }

    /**
     * @return array|QueryDefinition[]
     */
    public function allQueries(): array
    {
        return $this->queries;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasType($name): bool
    {
        return array_key_exists($name, $this->types);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasMutation($name): bool
    {
        return array_key_exists($name, $this->mutations);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasQuery($name): bool
    {
        return array_key_exists($name, $this->queries);
    }

    /**
     * @param string $name
     *
     * @return ObjectDefinitionInterface
     *
     * @throws \UnexpectedValueException
     */
    public function getType($name)
    {
        if (!$this->hasType($name)) {
            throw new \UnexpectedValueException(sprintf('Does not exist a valid definition for "%s" type', $name));
        }

        return $this->types[$name];
    }

    /**
     * @param string $name
     *
     * @return MutationDefinition
     */
    public function getMutation($name): MutationDefinition
    {
        return $this->mutations[$name];
    }

    /**
     * @param string $name
     *
     * @return QueryDefinition
     */
    public function getQuery($name): QueryDefinition
    {
        return $this->queries[$name];
    }

    /**
     * @param ObjectDefinitionInterface $type
     *
     * @return DefinitionManager
     */
    public function addType(ObjectDefinitionInterface $type): DefinitionManager
    {
        if ($this->hasType($type->getName())) {
            throw new \RuntimeException(sprintf('Duplicate definition for type with name "%s"', $type->getName()));
        }
        $this->types[$type->getName()] = $type;

        if ($type instanceof InterfaceDefinition) {
            $this->interfaces[$type->getName()] = $type;
        }

        if ($type->getClass()) {
            $this->typeMap[$type->getName()] = $type->getClass();
        }

        return $this;
    }

    /**
     * @param MutationDefinition $mutation
     *
     * @return DefinitionManager
     */
    public function addMutation(MutationDefinition $mutation): DefinitionManager
    {
        if ($this->hasMutation($mutation->getName())) {
            throw new \RuntimeException(sprintf('Duplicate definition for query with name "%s"', $mutation->getName()));
        }
        $this->mutations[$mutation->getName()] = $mutation;

        return $this;
    }

    /**
     * @param QueryDefinition $query
     *
     * @return DefinitionManager
     */
    public function addQuery(QueryDefinition $query): DefinitionManager
    {
        if ($this->hasQuery($query->getName())) {
            throw new \RuntimeException(sprintf('Duplicate definition for query with name "%s"', $query->getName()));
        }
        $this->queries[$query->getName()] = $query;

        return $this;
    }
}
