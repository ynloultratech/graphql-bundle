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

use Doctrine\Common\Util\Inflector;
use Ynlo\GraphQLBundle\Annotation\Mutation;
use Ynlo\GraphQLBundle\Annotation\ObjectType;
use Ynlo\GraphQLBundle\Component\TaggedServices\TaggedServices;
use Ynlo\GraphQLBundle\Component\TaggedServices\TagSpecification;
use Ynlo\GraphQLBundle\Definition\ArgumentAwareInterface;
use Ynlo\GraphQLBundle\Definition\ExecutableDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Loader\DefinitionLoaderInterface;
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\NamespaceAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\Resolver\EmptyObjectResolver;

/**
 * Contains many endpoints containing different definitions for each one
 */
class DefinitionRegistry
{
    /**
     * @var TaggedServices
     */
    private $taggedServices;

    /**
     * @var Endpoint[]
     */
    private static $endpoints = [];

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var array
     */
    private $config = [];

    /**
     * DefinitionRegistry constructor.
     *
     * @param TaggedServices $taggedServices
     * @param null|string    $cacheDir
     * @param array          $config
     */
    public function __construct(TaggedServices $taggedServices, ?string $cacheDir = null, $config = [])
    {
        $this->taggedServices = $taggedServices;
        $this->cacheDir = $cacheDir;
        $this->config = $config;
    }

    /**
     * @param string $name
     *
     * @return Endpoint
     */
    public function getEndpoint($name = 'default'): Endpoint
    {
        if (!$name) {
            $name = 'default';
        }
        if (array_key_exists($name, self::$endpoints)) {
            return self::$endpoints[$name];
        }

        $endpoint = self::$endpoints[$name] = new Endpoint($name);

        $specifications = $this->getTaggedServices('graphql.definition_loader');
        foreach ($specifications as $specification) {
            $resolver = $specification->getService();
            if ($resolver instanceof DefinitionLoaderInterface) {
                $resolver->loadDefinitions($endpoint);
            }
        }

        $this->compile($endpoint);

        return $endpoint;
    }

    /**
     * Verify endpoint definitions and do some tasks to prepare the endpoint
     *
     * @param Endpoint $endpoint
     */
    private function compile(Endpoint $endpoint)
    {
        foreach ($endpoint->allTypes() as $type) {
            if ($type instanceof FieldsAwareDefinitionInterface) {
                $this->normalizeFields($endpoint, $type);
            }
        }

        foreach ($endpoint->allQueries() as $query) {
            $query->setType($this->normalizeType($endpoint, $query->getType()));
            if ($query instanceof ArgumentAwareInterface) {
                $this->normalizeArguments($endpoint, $query);
            }
        }

        foreach ($endpoint->allMutations() as $mutation) {
            $mutation->setType($this->normalizeType($endpoint, $mutation->getType()));
            if ($mutation instanceof ArgumentAwareInterface) {
                $this->normalizeArguments($endpoint, $mutation);
            }
        }

        $groupByBundle = $this->config['schema']['namespaces']['bundles']['enabled']  ?? true;
        $groupByNode = $this->config['schema']['namespaces']['nodes']['enabled']  ?? true;
        if ($groupByBundle || $groupByNode) {
            $endpoint->setQueries($this->namespaceDefinitions($endpoint->allQueries(), $endpoint));
            $endpoint->setMutations($this->namespaceDefinitions($endpoint->allMutations(), $endpoint));
        } else {
            //keep queries & mutations sorted by related node
            $sortedQueries = $this->sortQueries($endpoint->allQueries());
            $endpoint->setQueries($sortedQueries);

            $sortedMutations = $this->sortQueries($endpoint->allMutations());
            $endpoint->setMutations($sortedMutations);
        }
    }

    /**
     * @param ExecutableDefinitionInterface[] $definitions
     */
    private function namespaceDefinitions($definitions, Endpoint $endpoint)
    {
        $namespacedDefinitions = [];
        foreach ($definitions as $definition) {
            if (!$definition instanceof NamespaceAwareDefinitionInterface || !$definition->getNamespace()) {
                $namespacedDefinitions[] = $definition;
                continue;
            }

            $root = null;
            $parent = null;
            if ($definition->getNamespace()->getBundle()) {
                $bundleSuffix = $this->config['schema']['namespaces']['bundles']['suffix'] ?? 'Bundle';
                $name = lcfirst($definition->getNamespace()->getBundle());
                $typeName = ucfirst($name).$bundleSuffix;
                $root = $this->createRootNamespace(get_class($definition), $name, $typeName, $endpoint);
                $parent = $endpoint->getType($root->getType());
            }

            if ($definition->getNamespace()->getNode()) {
                $nodeName = $definition->getNamespace()->getNode();
                $name = Inflector::pluralize(lcfirst($nodeName));

                $querySuffix = $this->config['schema']['namespaces']['nodes']['query_suffix'] ?? 'Query';
                $mutationSuffix = $this->config['schema']['namespaces']['nodes']['mutation_suffix'] ?? 'Mutation';

                $typeName = ucfirst($nodeName).(($definition instanceof MutationDefinition) ? $mutationSuffix : $querySuffix);
                if (!$root) {
                    $root = $this->createRootNamespace(get_class($definition), $name, $typeName, $endpoint);
                    $parent = $endpoint->getType($root->getType());
                } elseif ($parent) {
                    $parent = $this->createChildNamespace($parent, $name, $typeName, $endpoint);
                }

                //remove node suffix on namespaced definitions
                $definition->setName(preg_replace(sprintf("/(\w+)%s$/", $nodeName), '$1', $definition->getName()));
                $definition->setName(preg_replace(sprintf("/(\w+)%s$/", Inflector::pluralize($nodeName)), '$1', $definition->getName()));

            }

            if ($root && $parent) {
                $this->addDefinitionToNamespace($parent, $definition);
                $namespacedDefinitions[] = $root;
            }
        }

        return $namespacedDefinitions;
    }

    /**
     * @param FieldsAwareDefinitionInterface $fieldsAwareDefinition
     * @param ExecutableDefinitionInterface  $definition
     */
    private function addDefinitionToNamespace(FieldsAwareDefinitionInterface $fieldsAwareDefinition, ExecutableDefinitionInterface $definition)
    {
        $field = new FieldDefinition();
        $field->setName($definition->getName());
        $field->setType($definition->getType());
        $field->setResolver($definition->getResolver());
        $field->setArguments($definition->getArguments());
        $field->setList($definition->isList());
        $field->setMetas($definition->getMetas());
        $fieldsAwareDefinition->addField($field);
    }

    /**
     * @param ObjectDefinitionInterface $parent   parent definition to add a child field
     * @param string                    $name     name of the field
     * @param string                    $typeName name of the type to create
     * @param Endpoint                  $endpoint Endpoint instance to extract definitions
     *
     * @return ObjectDefinition
     */
    private function createChildNamespace(ObjectDefinitionInterface $parent, $name, $typeName, Endpoint $endpoint): ObjectDefinition
    {
        $child = new FieldDefinition();
        $child->setName($name);
        $child->setResolver(EmptyObjectResolver::class);

        $type = new ObjectDefinition();
        $type->setName($typeName);
        if ($endpoint->hasType($type->getName())) {
            $type = $endpoint->getType($type->getName());
        } else {
            $endpoint->add($type);
        }

        $child->setType($type->getName());
        $parent->addField($child);

        return $type;
    }

    /**
     * @param string   $rootType Class of the root type to create QueryDefinition or MutationDefinition
     * @param string   $name     name of the root field
     * @param string   $typeName name for the root type
     * @param Endpoint $endpoint Endpoint interface to extract existent definitions
     *
     * @return ExecutableDefinitionInterface
     */
    private function createRootNamespace($rootType, $name, $typeName, Endpoint $endpoint): ExecutableDefinitionInterface
    {
        /** @var ExecutableDefinitionInterface $rootDefinition */
        $rootDefinition = new $rootType();
        $rootDefinition->setName($name);
        $rootDefinition->setResolver(EmptyObjectResolver::class);

        $type = new ObjectDefinition();
        $type->setName($typeName);
        if ($endpoint->hasType($type->getName())) {
            $type = $endpoint->getType($type->getName());
        } else {
            $endpoint->add($type);
        }

        $rootDefinition->setType($type->getName());

        return $rootDefinition;
    }

    /**
     * @param QueryDefinition[]|MutationDefinition[] $queries
     *
     * @return array
     */
    private function sortQueries($queries)
    {
        $sortedQueries = [];
        foreach ($queries as $query) {
            $name = $query->getName();
            $node = $query->getType();
            if ($query->hasMeta('node')) {
                $node = $query->getMeta('node');
            }
            $sortedQueries[$node.'_'.$name] = $query;
        }
        ksort($sortedQueries);

        return $sortedQueries;
    }

    /**
     * @param Endpoint               $endpoint
     * @param ArgumentAwareInterface $argumentAware
     */
    private function normalizeArguments(Endpoint $endpoint, ArgumentAwareInterface $argumentAware)
    {
        foreach ($argumentAware->getArguments() as $argument) {
            $argument->setType($this->normalizeType($endpoint, $argument->getType()));
            if (!$argument->getType()) {
                $msg = sprintf('The argument "%s" of "%s" does not have a valid type', $argument->getName(), $argumentAware->getName());
                throw new \RuntimeException($msg);
            }
        }
    }

    /**
     * @param Endpoint                       $endpoint
     * @param FieldsAwareDefinitionInterface $fieldsAwareDefinition
     */
    private function normalizeFields(Endpoint $endpoint, FieldsAwareDefinitionInterface $fieldsAwareDefinition)
    {
        foreach ($fieldsAwareDefinition->getFields() as $field) {
            $field->setType($this->normalizeType($endpoint, $field->getType()));
            if (!$field->getType()) {
                $msg = sprintf('The field "%s" of "%s" does not have a valid type', $field->getName(), $fieldsAwareDefinition->getName());
                throw new \RuntimeException($msg);
            }
            $this->normalizeArguments($endpoint, $field);
        }
    }

    /**
     * @param Endpoint    $endpoint
     * @param string|null $type
     *
     * @return null|string
     */
    private function normalizeType(Endpoint $endpoint, $type)
    {
        if ($type) {
            if (class_exists($type) || interface_exists($type)) {
                if ($endpoint->hasTypeForClass($type)) {
                    $type = $endpoint->getTypeForClass($type);
                }
            }
        }

        return $type;
    }

    /**
     * @param string $tag
     *
     * @return array|TagSpecification[]
     */
    private function getTaggedServices($tag): array
    {
        return $this->taggedServices->findTaggedServices($tag);
    }
}
