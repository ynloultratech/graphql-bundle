<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Loader\Annotation;

use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * Common trait used un multiple parsers
 */
trait AnnotationParserHelper
{
    /**
     * Get default name based in given class using naming convention
     *
     * @param \ReflectionClass $refClass
     * @param Endpoint         $endpoint
     *
     * @return string
     */
    public function getDefaultName(\ReflectionClass $refClass, Endpoint $endpoint): string
    {
        if ($endpoint->hasTypeForClass($refClass->getName())) {
            return $endpoint->getTypeForClass($refClass->getName());
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
     * @param \ReflectionClass $refClass
     * @param Endpoint         $endpoint
     *
     * @return ObjectDefinitionInterface
     */
    public function getObjectDefinition(\ReflectionClass $refClass, Endpoint $endpoint): ObjectDefinitionInterface
    {
        if ($endpoint->hasTypeForClass($refClass->getName())) {
            return $endpoint->getType($endpoint->getTypeForClass($refClass->getName()));
        }

        $objectType = null;
        preg_match('/(\w+)(\\\\w+)?\\\\(\w+)$/', $refClass->getName(), $matches);
        if (!isset($matches[1]) || !$endpoint->hasType($matches[1])) {
            $error = sprintf('Can`t resolve a valid object type for "%s"', $refClass->getName());
            throw new \RuntimeException($error);
        }

        return $endpoint->getType($matches[1]);
    }
}
