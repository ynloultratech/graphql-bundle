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

use Ynlo\GraphQLBundle\Definition\ClassAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\InterfaceDefinition;
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Definition\SubscriptionDefinition;
use Ynlo\GraphQLBundle\Resolver\EmptyObjectResolver;

/**
 * Class Endpoint
 */
class Endpoint
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var DefinitionInterface[]
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
     * @var SubscriptionDefinition[]
     */
    protected $subscriptions = [];

    /**
     * Map subscriptions names to a resolver
     *
     * @var string[]
     */
    protected $subscriptionsMap = [];

    /**
     * Endpoint constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return DefinitionInterface[]
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
        return \in_array($class, $this->typeMap);
    }

    /**
     * @param string $class
     *
     * @return array|string[]
     */
    public function getTypesForClass(string $class): array
    {
        $types = array_filter(
            $this->typeMap,
            function ($val) use ($class) {
                return $val === $class;
            }
        );

        if (empty($types)) {
            $error = sprintf('Does not exist any valid type for class "%s"', $class);
            throw new \UnexpectedValueException($error);
        }

        return array_keys($types);
    }

    /**
     * Return the first type representing this class
     *
     * NOTE! a class can be represented by many types use `getTypesForClass`
     * to get all possible types.
     *
     * @param string $class
     *
     * @return string
     *
     * @throws \UnexpectedValueException if the class does not have any valid type
     */
    public function getTypeForClass(string $class): string
    {
        return $this->getTypesForClass($class)[0];
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
     * @return array|SubscriptionDefinition[]
     */
    public function allSubscriptions(): array
    {
        return $this->subscriptions;
    }

    /**
     * @return string[]
     */
    public function getSubscriptionsResolvers(): array
    {
        return $this->subscriptionsMap;
    }

    /**
     * @param string $resolver
     *
     * @return string
     */
    public function getSubscriptionNameForResolver($resolver): ?string
    {
        $byResolver = array_flip($this->subscriptionsMap);

        return $byResolver[$resolver] ?? null;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getSubscriptionResolver($name): ?string
    {
        return $this->subscriptionsMap[$name] ?? null;
    }

    /**
     * @param DefinitionInterface[] $types
     */
    public function setTypes(array $types)
    {
        $this->types = [];
        $this->interfaces = [];
        foreach ($types as $type) {
            $this->addType($type);
        }
    }

    /**
     * @param MutationDefinition[] $mutations
     */
    public function setMutations(array $mutations)
    {
        $this->mutations = [];
        foreach ($mutations as $mutation) {
            $this->addMutation($mutation);
        }
    }

    /**
     * @param QueryDefinition[] $queries
     */
    public function setQueries(array $queries)
    {
        $this->queries = [];
        foreach ($queries as $query) {
            $this->addQuery($query);
        }
    }

    /**
     * @param SubscriptionDefinition[] $subscriptions
     */
    public function setSubscriptions(array $subscriptions)
    {
        $this->subscriptions = [];
        foreach ($subscriptions as $subscription) {
            $this->addSubscription($subscription);
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasType($name): bool
    {
        //in case pass FQN class name resolve the first matching type
        if ($name && (class_exists($name) || interface_exists($name))) {
            if ($this->hasTypeForClass($name)) {
                $name = $this->getTypesForClass($name)[0];
            }
        }

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
     * @return bool
     */
    public function hasSubscription($name): bool
    {
        return array_key_exists($name, $this->subscriptions);
    }

    /**
     * @param string $name
     *
     * @return Endpoint
     */
    public function removeType($name): Endpoint
    {
        if ($this->hasType($name)) {
            $type = $this->getType($name);
            if ($type instanceof InterfaceDefinition) {
                unset($this->interfaces[$type->getName()]);
            }
        }

        unset($this->types[$name]);
        if (isset($this->typeMap[$name])) {
            unset($this->typeMap[$name]);
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return Endpoint
     */
    public function removeQuery($name): Endpoint
    {
        unset($this->queries[$name]);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return Endpoint
     */
    public function removeMutation($name): Endpoint
    {
        unset($this->mutations[$name]);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return Endpoint
     */
    public function removeSubscription($name): Endpoint
    {
        unset($this->subscriptions[$name]);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return DefinitionInterface
     *
     * @throws \UnexpectedValueException
     */
    public function getType($name)
    {
        if ($name && (class_exists($name) || interface_exists($name))) {
            if ($this->hasTypeForClass($name)) {
                $name = $this->getTypeForClass($name);
            }
        }

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
     * @return SubscriptionDefinition
     */
    public function getSubscription($name): SubscriptionDefinition
    {
        return $this->subscriptions[$name];
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
     * @param DefinitionInterface $definition
     *
     * @return Endpoint
     */
    public function add(DefinitionInterface $definition): Endpoint
    {
        if ($definition instanceof MutationDefinition) {
            return $this->addMutation($definition);
        }

        if ($definition instanceof QueryDefinition) {
            return $this->addQuery($definition);
        }

        return $this->addType($definition);
    }

    /**
     * @param DefinitionInterface $type
     *
     * @return Endpoint
     */
    public function addType(DefinitionInterface $type): Endpoint
    {
        if ($this->hasType($type->getName())) {
            throw new \RuntimeException(sprintf('Duplicate definition for type with name "%s"', $type->getName()));
        }
        $this->types[$type->getName()] = $type;

        if ($type instanceof InterfaceDefinition) {
            $this->interfaces[$type->getName()] = $type;
        }

        if ($type instanceof ClassAwareDefinitionInterface) {
            if ($type->getClass()) {
                $class = $type->getClass();
                //all classes are saved without \ at the beginning
                $class = preg_replace('/^\\\\/', '', $class);
                $this->typeMap[$type->getName()] = $class;
            }
        }

        return $this;
    }

    /**
     * @param MutationDefinition $mutation
     *
     * @return Endpoint
     */
    public function addMutation(MutationDefinition $mutation): Endpoint
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
     * @return Endpoint
     */
    public function addQuery(QueryDefinition $query): Endpoint
    {
        if ($this->hasQuery($query->getName())) {
            throw new \RuntimeException(sprintf('Duplicate definition for query with name "%s"', $query->getName()));
        }
        $this->queries[$query->getName()] = $query;

        return $this;
    }

    /**
     * @param SubscriptionDefinition $subscription
     *
     * @return Endpoint
     */
    public function addSubscription(SubscriptionDefinition $subscription): Endpoint
    {
        if ($this->hasSubscription($subscription->getName())) {
            throw new \RuntimeException(sprintf('Duplicate definition for subscription with name "%s"', $subscription->getName()));
        }
        if (!is_a($subscription->getResolver(), EmptyObjectResolver::class, true)) {
            $this->subscriptionsMap[$subscription->getName()] = $subscription->getResolver();
        }
        $this->subscriptions[$subscription->getName()] = $subscription;

        return $this;
    }
}
