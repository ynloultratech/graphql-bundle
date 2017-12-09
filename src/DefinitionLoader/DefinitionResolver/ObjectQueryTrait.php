<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DefinitionLoader\DefinitionResolver;

use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;

/**
 * Trait ObjectQueryTrait
 */
trait ObjectQueryTrait
{
    /**
     * Get default name based in given class using naming convention
     *
     * @param \ReflectionClass  $refClass
     * @param DefinitionManager $definitionManager
     *
     * @return string
     */
    public function getDefaultName(\ReflectionClass $refClass, DefinitionManager $definitionManager): string
    {
        if ($definitionManager->hasTypeForClass($refClass->getName())) {
            return $definitionManager->getTypeForClass($refClass->getName());
        }

        preg_match('/\w+$/', $refClass->getName(), $matches);

        return lcfirst($matches[0] ?? '');
    }

    /**
     * Get object type using naming convention
     * if Query is placed under User\AllUsers namespace, then "User" is the object type
     *
     * Mutation\User\UpdateUser -> User
     * Query\User\Users -> User
     * Form\Input\User\AddUserInput -> User
     *
     * @param \ReflectionClass  $refClass
     * @param DefinitionManager $definitionManager
     *
     * @return ObjectDefinitionInterface
     */
    public function getObjectDefinition(\ReflectionClass $refClass, DefinitionManager $definitionManager): ObjectDefinitionInterface
    {
        if ($definitionManager->hasTypeForClass($refClass->getName())) {
            return $definitionManager->getType($definitionManager->getTypeForClass($refClass->getName()));
        }

        $objectType = null;
        preg_match('/(\w+)(\\\\w+)?\\\\(\w+)$/', $refClass->getName(), $matches);
        if (!isset($matches[1]) || !$definitionManager->hasType($matches[1])) {
            $error = sprintf('Can`t resolve a valid object type for "%s"', $refClass->getName());
            throw new \RuntimeException($error);
        }

        return $definitionManager->getType($matches[1]);
    }
}
