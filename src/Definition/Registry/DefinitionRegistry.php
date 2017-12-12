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

use Ynlo\GraphQLBundle\Component\TaggedServices\TaggedServices;
use Ynlo\GraphQLBundle\Component\TaggedServices\TagSpecification;
use Ynlo\GraphQLBundle\Definition\ArgumentAwareInterface;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Loader\DefinitionLoaderInterface;
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;

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
     * DefinitionRegistry constructor.
     *
     * @param TaggedServices $taggedServices
     * @param null|string    $cacheDir
     */
    public function __construct(TaggedServices $taggedServices, ?string $cacheDir = null)
    {
        $this->taggedServices = $taggedServices;
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param string $name
     *
     * @return Endpoint
     */
    public function getEndpoint($name = 'default'): Endpoint
    {
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

        //keep queries & mutations sorted by related node
        $sortedQueries = $this->sortQueries($endpoint->allQueries());
        $endpoint->setQueries($sortedQueries);

        $sortedMutations = $this->sortQueries($endpoint->allMutations());
        $endpoint->setMutations($sortedMutations);
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
            $sortedQueries[$node.$name] = $query;
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
