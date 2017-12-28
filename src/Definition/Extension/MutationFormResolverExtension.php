<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Extension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\NodeAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Form\Type\GraphQLType;
use Ynlo\GraphQLBundle\Form\Type\IDType;
use Ynlo\GraphQLBundle\Type\Types;
use Ynlo\GraphQLBundle\Util\ClassUtils;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * MutationFormResolverExtension
 */
class MutationFormResolverExtension extends AbstractDefinitionExtension
{
    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @param FormFactory $formFactory
     */
    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'form';
    }

    /**
     * {@inheritDoc}
     */
    public function buildConfig(ArrayNodeDefinition $root)
    {
        $config = $root
            ->info('Resolve the form to use as input for mutations')
            ->addDefaultsIfNotSet()
            ->canBeDisabled()
            ->children();

        $config->variableNode('type')
               ->defaultNull()
               ->info(
                   'Specify the form type to use,
[string] Name of the form type to use
[true|null] The form will be automatically resolved to ...Bundle\Form\Input\{Node}\{MutationName}Input.
[true] Throw a exception if the form can`t be located
[false] The form is not required and should not be resolved'
               );
        $config->variableNode('options')->defaultValue([])->info('Form options');
        $config->variableNode('argument')
               ->defaultValue('input')
               ->info('Name of the argument to use as input');

        $config->booleanNode('client_mutation_id')
               ->defaultTrue()
               ->info('Automatically add a field called clientMutationId');
    }

    /**
     * {@inheritDoc}
     */
    public function configure(DefinitionInterface $definition, Endpoint $endpoint, array $config)
    {
        if (!$config || !$config['enabled'] || !$definition instanceof MutationDefinition) {
            return;
        }

        $formType = $config['type'] ?? null;

        //the related class is used to match a form using naming conventions
        $relatedClass = null;
        if ($definition instanceof NodeAwareDefinitionInterface && $definition->getNode()) {
            $relatedClass = $definition->getNode();
            if ($class = $endpoint->getClassForType($relatedClass)) {
                $relatedClass = $class;
            }
        } elseif ($definition->getResolver()) {
            $relatedClass = $definition->getResolver();
        }

        //try find the form using a related class
        if ($relatedClass && (!$formType || true === $formType)) {
            $bundleNamespace = ClassUtils::relatedBundleNamespace($relatedClass);
            $formClass = ClassUtils::applyNamingConvention(
                $bundleNamespace,
                'Form\Input',
                $definition->getNode(),
                ucfirst($definition->getName()),
                'Input'
            );
            if (class_exists($formClass)) {
                $formType = $formClass;
            } elseif (true === $formType) {
                $error = sprintf(
                    'Can`t find a valid input form type to use in "%s".
                         Create the form "%s" or specify a custom form',
                    $definition->getName(),
                    $formClass
                );
                throw new \Exception($error);
            }
        }

        if ($formType) {
            $config['type'] = $formType;

            $form = $this->formFactory->create($formType, null, $config['options'] ?? []);
            $inputObject = $this->createFormInputObject($endpoint, $form, ucfirst($definition->getName()));
            $endpoint->addType($inputObject);

            $input = new ArgumentDefinition();
            $input->setName($config['argument']);
            $input->setType($inputObject->getName());

            if ($config['client_mutation_id']) {
                $clientMutationId = new FieldDefinition();
                $clientMutationId->setName('clientMutationId');
                $clientMutationId->setType(Types::STRING);
                $clientMutationId->setDescription('A unique identifier for the client performing the mutation.');
                $inputObject->prependField($clientMutationId);
            }

            $definition->addArgument($input);
            $definition->setMeta('form', $config);
        }
    }

    /**
     * @param Endpoint      $endpoint
     * @param FormInterface $form
     * @param string        $name
     *
     * @return InputObjectDefinition
     */
    public function createFormInputObject(Endpoint $endpoint, FormInterface $form, $name)
    {
        $inputObject = new InputObjectDefinition();
        $inputObject->setName($name.'Input');

        foreach ($form->all() as $formField) {
            $field = new FieldDefinition();
            $field->setName($formField->getConfig()->getOption('label') ?? $formField->getName());
            $field->setNonNull($formField->isRequired());
            $field->setOriginName($formField->getName());

            if ($formField->all()) {
                $childName = $name.ucfirst($formField->getName());
                $child = $this->createFormInputObject($endpoint, $formField, $childName);
                $endpoint->addType($child);
                $field->setType($child->getName());
            } else {
                $this->resolveFormFieldDefinition($field, $formField);
            }

            $inputObject->addField($field);
        }

        return $inputObject;
    }

    /**
     * @param FieldDefinition $field
     * @param FormInterface   $form
     */
    public function resolveFormFieldDefinition(FieldDefinition $field, FormInterface $form)
    {
        $type = null;
        $resolver = $form->getConfig()->getType()->getOptionsResolver();
        if ($resolver->hasDefault('graphql_type')) {
            $type = $resolver->resolve([])['graphql_type'];
            $field->setList(TypeUtil::isTypeList($type));
            $type = TypeUtil::normalize($type);
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), GraphQLType::class, true)) {
            $type = $form->getConfig()->getOptions()['graphql_type'];
            $field->setList(TypeUtil::isTypeList($type));
            $type = TypeUtil::normalize($type);
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), IDType::class, true)) {
            if ($form->getConfig()->hasOption('multiple') && $form->getConfig()->getOption('multiple')) {
                $field->setList(true);
            }
            $type = Types::ID;
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), TextType::class, true)) {
            $type = Types::STRING;
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), TextareaType::class, true)) {
            $type = Types::STRING;
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), EmailType::class, true)) {
            $type = Types::STRING;
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), CheckboxType::class, true)) {
            $type = Types::BOOLEAN;
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), IntegerType::class, true)) {
            $type = Types::INT;
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), NumberType::class, true)) {
            $type = Types::FLOAT;
        }

        if (!$type) {
            $error = sprintf(
                'The field "%s" in the parent form "%s" does not have a valid type. 
                If your are using a custom type, must define a option called "graphql_type" to resolve the form to a valid GraphQL type',
                $form->getName(),
                $form->getParent()->getName()
            );
            throw new \InvalidArgumentException($error);
        }

        $field->setType($type);
    }
}
