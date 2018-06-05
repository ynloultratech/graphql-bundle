<?php

/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Plugin;

use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Definition\TypeAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Type\Registry\TypeRegistry;

class SchemaValidatorDefinitionPlugin extends AbstractDefinitionPlugin
{
    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * @inheritDoc
     */
    public function configureEndpoint(Endpoint $endpoint): void
    {
        $this->endpoint = $endpoint;

        //run extensions recursively in all types and fields
        foreach ($endpoint->allTypes() as $type) {
            $this->validateDefinition($type);
            if ($type instanceof FieldsAwareDefinitionInterface) {
                foreach ($type->getFields() as $field) {
                    $this->validateDefinition($field, $type->getName());
                    foreach ($field->getArguments() as $argument) {
                        $this->validateDefinition($argument, sprintf('%s.%s', $type->getName(), $field->getName()));
                    }
                }
            }
        }

        //run extension in all queries
        foreach ($endpoint->allQueries() as $query) {
            $this->validateDefinition($query);
            foreach ($query->getArguments() as $argument) {
                $this->validateDefinition($argument, $query->getName());
            }
        }

        //run extensions in all mutations
        foreach ($endpoint->allMutations() as $mutation) {
            $this->validateDefinition($mutation);
            foreach ($mutation->getArguments() as $argument) {
                $this->validateDefinition($argument, $mutation->getName());
            }
        }
    }

    protected function validateDefinition(DefinitionInterface $definition, string $context = null)
    {
        if ($context) {
            $path = sprintf('%s.%s', $context, $definition->getName());
        } else {
            $path = $definition->getName();
        }

        if ($definition instanceof TypeAwareDefinitionInterface) {
            if (!$definition->getType()) {
                throw new \RuntimeException(sprintf('"%s" does not have any type configured.', $path));
            }

            try {
                TypeRegistry::has($definition->getType());
            } catch (\UnexpectedValueException $exception) {
                throw new \RuntimeException(sprintf('The type "%s" used in "%s" is not a valid registered type.', $definition->getType(), $path));
            }
        }
    }
}
