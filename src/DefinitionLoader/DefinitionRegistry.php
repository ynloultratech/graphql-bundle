<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DefinitionLoader;

use Ynlo\GraphQLBundle\Component\TaggedServices\TaggedServices;
use Ynlo\GraphQLBundle\Component\TaggedServices\TagSpecification;
use Ynlo\GraphQLBundle\Definition\ArgumentAwareInterface;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;

/**
 * Class DefinitionRegistry
 */
class DefinitionRegistry
{
    /**
     * @var TaggedServices
     */
    private $taggedServices;

    /**
     * @var DefinitionManager[]
     */
    private static $manager = [];

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
     * @return DefinitionManager
     */
    public function getManager($name = 'default'): DefinitionManager
    {
        if (array_key_exists($name, self::$manager)) {
            return self::$manager[$name];
        }

        $manager = self::$manager[$name] = new DefinitionManager($name);

        $specifications = $this->getTaggedServices('graphql.definition_loader');
        foreach ($specifications as $specification) {
            $resolver = $specification->getService();
            if ($resolver instanceof DefinitionLoaderInterface) {
                $resolver->loadDefinitions($manager);
            }
        }

        $this->compile($manager);

        return $manager;
    }

    /**
     * Verify the manager definitions and do some tasks to prepare the manager
     *
     * @param DefinitionManager $manager
     */
    private function compile(DefinitionManager $manager)
    {
        foreach ($manager->allTypes() as $type) {
            if ($type instanceof FieldsAwareDefinitionInterface) {
                $this->normalizeFields($manager, $type);
            }
        }

        foreach ($manager->allQueries() as $query) {
            $query->setType($this->normalizeType($manager, $query->getType()));
            if ($query instanceof ArgumentAwareInterface) {
                $this->normalizeArguments($manager, $query);
            }
        }

        foreach ($manager->allMutations() as $mutation) {
            $mutation->setType($this->normalizeType($manager, $mutation->getType()));
            if ($mutation instanceof ArgumentAwareInterface) {
                $this->normalizeArguments($manager, $mutation);
            }
        }
    }

    /**
     * @param DefinitionManager      $manager
     * @param ArgumentAwareInterface $argumentAware
     */
    private function normalizeArguments(DefinitionManager $manager, ArgumentAwareInterface $argumentAware)
    {
        foreach ($argumentAware->getArguments() as $argument) {
            $argument->setType($this->normalizeType($manager, $argument->getType()));
            if (!$argument->getType()) {
                $msg = sprintf('The argument "%s" of "%s" does not have a valid type', $argument->getName(), $argumentAware->getName());
                throw new \RuntimeException($msg);
            }
        }
    }

    /**
     * @param DefinitionManager              $manager
     * @param FieldsAwareDefinitionInterface $fieldsAwareDefinition
     */
    private function normalizeFields(DefinitionManager $manager, FieldsAwareDefinitionInterface $fieldsAwareDefinition)
    {
        foreach ($fieldsAwareDefinition->getFields() as $field) {
            $field->setType($this->normalizeType($manager, $field->getType()));
            if (!$field->getType()) {
                $msg = sprintf('The field "%s" of "%s" does not have a valid type', $field->getName(), $fieldsAwareDefinition->getName());
                throw new \RuntimeException($msg);
            }
            $this->normalizeArguments($manager, $field);
        }
    }

    /**
     * @param DefinitionManager $manager
     * @param string|null       $type
     *
     * @return null|string
     */
    private function normalizeType(DefinitionManager $manager, $type)
    {
        if ($type) {
            if (class_exists($type) || interface_exists($type)) {
                if ($manager->hasTypeForClass($type)) {
                    $type = $manager->getTypeForClass($type);
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
