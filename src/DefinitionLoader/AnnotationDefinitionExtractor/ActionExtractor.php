<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DefinitionLoader\AnnotationDefinitionExtractor;

use GraphQL\Type\Definition\Type;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Annotation\Query;
use Ynlo\GraphQLBundle\Definition\ActionDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinition;
use Ynlo\GraphQLBundle\Definition\ObjectDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\QueryDefinition;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;

/**
 * Extract mutations and queries definitions
 */
class ActionExtractor extends ObjectExtractor
{
    private const FIELD_MUTATION_ID = 'clientMutationId';
    private const FIELD_MUTATION_ID_TYPE = Type::STRING;
    private const FIELD_MUTATION_ID_DESCRIPTION = 'A unique identifier for the client performing the mutation.';

    /**
     * {@inheritDoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Query || $annotation instanceof Annotation\Mutation;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($annotation, \ReflectionClass $refClass, DefinitionManager $definitionManager)
    {
        if ($annotation instanceof Annotation\Mutation) {
            $actionDefinition = new MutationDefinition();
        } else {
            $actionDefinition = new QueryDefinition();
        }

        if ($definitionManager->hasQuery($annotation->name)
            || $definitionManager->hasMutation($annotation->name)
        ) {
            return;
        }

        $actionDefinition->setName($annotation->name);
        $actionDefinition->setNodeType($this->getNormalizedType($annotation->type));

        if ($annotation instanceof Annotation\Query) {
            $actionDefinition->setReturnType($this->getNormalizedType($annotation->type));
            $actionDefinition->setReturnList($this->isTypeList($annotation->type));
        }

        $actionDefinition->setDeprecationReason($annotation->deprecationReason);

        $resolver = $annotation->resolver;
        if (!$resolver && $refClass->hasMethod('__invoke')) {
            $resolver = $refClass->getName();
        }
        $actionDefinition->setResolver($resolver);
        $actionDefinition->setDescription($annotation->description);

        if ($annotation instanceof Annotation\Mutation) {
            $actionDefinition->setValidationGroups($annotation->validationGroups);

            $inputType = null;
            $inputName = ucfirst($actionDefinition->getName().'Input');

            //create from input definition
            if ($annotation->input) {
                $inputTypeBase = $definitionManager->getType($annotation->input);
                //create input definition from given object definition
                if ($inputTypeBase instanceof ObjectDefinitionInterface) {
                    if (!$definitionManager->hasType($inputName)) {
                        $inputType = $this->createInputTypeFromObject($definitionManager, $inputTypeBase);
                        $inputType->setName($inputName);
                        $inputType->setDescription("Input argument type of **{$annotation->name}**");
                        $definitionManager->addType($inputType);
                    }
                }
            }

            //add extra arguments or create new input
            if ($annotation->argsToInput && $annotation->args) {
                if ($actionDefinition->hasArg('input')) {
                    $inputType = $definitionManager->getType($actionDefinition->getArg('input'));
                } elseif ($definitionManager->hasType($inputName)) {
                    $inputType = $definitionManager->getType($inputName);
                } else {
                    $inputType = new InputObjectDefinition();
                    $inputType->setName($inputName);
                    $definitionManager->addType($inputType);
                }

                /** @var Annotation\Arg $arg */
                foreach (array_reverse($annotation->args) as $arg) {
                    $field = new FieldDefinition();
                    $field->setName($arg->name);
                    $field->setDescription($arg->description);
                    $field->setOriginName($arg->internalName);
                    $field->setType($this->getNormalizedType($arg->type));
                    $field->setList($this->isTypeList($arg->type));
                    $field->setNonNull($this->isTypeNonNull($arg->type));
                    $field->setNonNullList($this->isTypeNonNullList($arg->type));
                    $inputType->prependField($field);
                }
                $annotation->args = [];//clear
            }

            if (null !== $inputType) {
                if ($annotation->addMutationId) {
                    $mutationId = new FieldDefinition();
                    $mutationId->setName(self::FIELD_MUTATION_ID);
                    $mutationId->setDescription(self::FIELD_MUTATION_ID_DESCRIPTION);
                    $mutationId->setType(self::FIELD_MUTATION_ID_TYPE);
                    $inputType->prependField($mutationId);
                }

                $arg = new ArgumentDefinition();
                $arg->setName('input');
                $arg->setType($inputType->getName());
                $arg->setNonNull(true);
                $actionDefinition->addArg($arg);
            }
        }

        /** @var Annotation\Arg $argAnnotation */
        foreach ($annotation->args as $argAnnotation) {
            $arg = new ArgumentDefinition();
            $arg->setName($argAnnotation->name);
            $arg->setDescription($argAnnotation->description);
            $arg->setType($this->getNormalizedType($argAnnotation->type));
            $arg->setList($this->isTypeList($argAnnotation->type));
            $arg->setNonNull($this->isTypeNonNull($argAnnotation->type));
            $arg->setNonNullList($this->isTypeNonNullList($argAnnotation->type));
            $arg->setDefaultValue($argAnnotation->defaultValue);
            $arg->setInternalName($argAnnotation->internalName);
            $actionDefinition->addArg($arg);
        }

        if ($annotation instanceof Annotation\Mutation && $annotation->returns && !$actionDefinition->getReturnType()) {
            if (\is_array($annotation->returns)) {
                $this->createPayloadFromReturns(
                    $annotation->returns,
                    $actionDefinition,
                    $definitionManager,
                    $annotation->addMutationId
                );
            } else {
                $actionDefinition->setReturnType($this->getNormalizedType($annotation->returns));
                $actionDefinition->setReturnList($this->isTypeList($annotation->returns));
            }
        }

        if ($annotation instanceof Annotation\Mutation) {
            $definitionManager->addMutation($actionDefinition);
        } else {
            $definitionManager->addQuery($actionDefinition);
        }
    }

    /**
     * Create input type for given object, used to add/update objects
     *
     * @param DefinitionManager         $definitionManager
     * @param ObjectDefinitionInterface $objectDefinition
     *
     * @return InputObjectDefinition
     */
    private function createInputTypeFromObject(DefinitionManager $definitionManager, ObjectDefinitionInterface $objectDefinition)
    {
        $inputType = new InputObjectDefinition();
        $inputType->setName(sprintf('%sInput', $objectDefinition->getName()));
        $inputType->setDescription(sprintf('Input for %s', $objectDefinition->getName()));

        foreach ($objectDefinition->getFields() as $field) {
            if ($field->isReadOnly()) {
                continue;
            }

            $inputField = clone $field;

            //when create a input object based on existent object
            //a nonNull field not necessarily is NonNull during input
            //for example the user password is a good example of NonNull object.
            //When the user is updated, should not be required enter the password every time
            //TODO: find some way to mark a field as required in the GraphQl schema for ADD operations
            if ($inputField->isNonNull()) {
                $inputField->setNonNull(false);
            }

            //relations
            if ($definitionManager->hasType($inputField->getType())) {
                if (!$field->getInputRelation()) {
                    continue;
                }

                if ($inputField->getInputRelation() === FieldDefinition::INPUT_BY_ID) {
                    $inputField->setName($field->getName().'Id');
                    $inputField->setType(Type::ID);
                    $inputType->addField($inputField);
                }

                if ($field->getInputRelation() === FieldDefinition::INPUT_INLINE) {
                    $input = $this->createInputTypeFromObject($definitionManager, $definitionManager->getType($field->getType()));
                    if (!$definitionManager->hasType($input->getName())) {
                        $definitionManager->addType($input);
                    }
                    $inputField->setType($input->getName());
                }
            }

            $inputType->addField($inputField);
        }

        $inputType->setClass($objectDefinition->getClass());

        return $inputType;
    }

    /**
     * Create a Payload object based on return fields
     *
     * @param Annotation\Field[]        $returns
     * @param ActionDefinitionInterface $actionDefinition
     * @param DefinitionManager         $definitionManager
     * @param bool                      $addMutationId
     */
    private function createPayloadFromReturns(array $returns, ActionDefinitionInterface $actionDefinition, DefinitionManager $definitionManager, $addMutationId = true)
    {
        $outputType = new ObjectDefinition();
        $outputType->setName(ucfirst($actionDefinition->getName().'Payload'));
        $actionDefinition->setReturnType("{$outputType->getName()}");
        $outputType->setDescription("Return type of **{$actionDefinition->getName()}**");
        $definitionManager->addType($outputType);

        if ($addMutationId) {
            $returns[] = new Annotation\Field(
                [
                    'name' => self::FIELD_MUTATION_ID,
                    'type' => self::FIELD_MUTATION_ID_TYPE,
                    'description' => self::FIELD_MUTATION_ID_DESCRIPTION,
                ]
            );
        }

        /** @var Annotation\Field $returnField */
        foreach ($returns as $returnField) {
            $field = new FieldDefinition();
            $field->setName($returnField->name);
            $field->setDescription($returnField->description);
            $field->setType($this->getNormalizedType($returnField->type));
            $field->setList($this->isTypeList($returnField->type));
            $field->setNonNull($this->isTypeNonNull($returnField->type));
            $field->setNonNullList($this->isTypeNonNullList($returnField->type));
            $outputType->addField($field);
        }
    }
}
