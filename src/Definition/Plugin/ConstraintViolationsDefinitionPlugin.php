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

use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\ConstraintViolation;
use Ynlo\GraphQLBundle\Model\ConstraintViolationParameter;

/**
 * This plugin remove the field constraintViolations and all related types
 * when violations should be located only in errors.
 */
class ConstraintViolationsDefinitionPlugin extends AbstractDefinitionPlugin
{
    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function configureEndpoint(Endpoint $endpoint): void
    {
        if (($this->config['validation_messages'] ?? null) !== 'error') {
            return;
        }

        if ($endpoint->hasTypeForClass(ConstraintViolation::class)) {
            $violationType = $endpoint->getType(ConstraintViolation::class);
            foreach ($endpoint->allTypes() as $type) {
                if ($type instanceof FieldsAwareDefinitionInterface) {
                    foreach ($type->getFields() as $field) {
                        if ($endpoint->hasType($field->getType())) {
                            if ($endpoint->getType($field->getType())->getName() === $violationType->getName()) {
                                $type->removeField($field->getName());
                            }
                        }
                    }
                }
            }

            $endpoint->removeType($violationType->getName());
        }

        if ($endpoint->hasTypeForClass(ConstraintViolationParameter::class)) {
            $endpoint->removeType($endpoint->getTypeForClass(ConstraintViolationParameter::class));
        }
    }
}
