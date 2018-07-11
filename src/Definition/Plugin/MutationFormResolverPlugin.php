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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\NodeAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Form\Input\InputFieldTypeGuesser;
use Ynlo\GraphQLBundle\Type\Types;
use Ynlo\GraphQLBundle\Util\ClassUtils;
use Ynlo\GraphQLBundle\Util\TypeUtil;

/**
 * MutationFormResolverPlugin
 */
class MutationFormResolverPlugin extends AbstractDefinitionPlugin
{
    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var InputFieldTypeGuesser[]|iterable
     */
    protected $typeGuessers;

    /**
     * MutationFormResolverPlugin constructor.
     *
     * @param FormFactory                      $formFactory
     * @param iterable|InputFieldTypeGuesser[] $typeGuessers
     */
    public function __construct(FormFactory $formFactory, iterable $typeGuessers = [])
    {
        $this->formFactory = $formFactory;
        $this->typeGuessers = $typeGuessers;
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
    public function buildConfig(ArrayNodeDefinition $root): void
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
    public function configure(DefinitionInterface $definition, Endpoint $endpoint, array $config): void
    {
        if (!$definition instanceof MutationDefinition || !isset($config['enabled'])) {
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
        if ($relatedClass && (!$formType || true === $formType) && $definition->getNode()) {
            $bundleNamespace = ClassUtils::relatedBundleNamespace($relatedClass);
            $nodeName = $endpoint->getType($definition->getNode())->getName();
            $formClass = ClassUtils::applyNamingConvention(
                $bundleNamespace,
                'Form\Input',
                $nodeName,
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
                throw new \RuntimeException($error);
            }
        }

        if ($formType) {
            $config['type'] = $formType;

            $form = $this->formFactory->create($formType, null, $config['options'] ?? []);
            $inputObject = $this->createFormInputObject($endpoint, $form, ucfirst($definition->getName()));
            $endpoint->addType($inputObject);

            $input = new ArgumentDefinition();
            $input->setName($config['argument'] ?? 'input');
            $input->setType($inputObject->getName());

            if ($config['client_mutation_id'] ?? true) {
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
    private function createFormInputObject(Endpoint $endpoint, FormInterface $form, string $name): InputObjectDefinition
    {
        $inputObject = new InputObjectDefinition();
        $inputObject->setName($name.'Input');

        foreach ($form->all() as $formField) {
            $field = new FieldDefinition();
            $label = $formField->getConfig()->getOption('label');
            $field->setName(!empty($label) ? $label : $formField->getName());
            $field->setDescription($formField->getConfig()->getOption('graphql_description') ?? null);
            $field->setDeprecationReason($formField->getConfig()->getOption('graphql_deprecation_reason') ?? null);
            $field->setNonNull($formField->isRequired());
            $field->setOriginName($formField->getName());

            if ($formField->all()) {
                $childName = $name.ucfirst($formField->getName());
                $child = $this->createFormInputObject($endpoint, $formField, $childName);
                $endpoint->addType($child);
                $field->setType($child->getName());
            } elseif (is_a($formField->getConfig()->getType()->getInnerType(), CollectionType::class)) {
                $childName = $name.ucfirst($formField->getName());
                $childFormType = $formField->getConfig()->getOptions()['entry_type'];
                $childFormOptions = $formField->getConfig()->getOptions()['entry_options'];
                $childForm = $this->formFactory->create($childFormType, null, $childFormOptions ?? []);
                $childForm->setParent($form);
                try {
                    //resolve type if is a valid scalar type or predefined type
                    $this->resolveFormFieldDefinition($field, $childForm);
                    $field->setList(true);
                } catch (\InvalidArgumentException $exception) {
                    //on exception, try build a child form for this collection
                    $child = $this->createFormInputObject($endpoint, $childForm, $childName);
                    $field->setType($child->getName());
                    $field->setList(true);
                    $endpoint->add($child);
                }
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
    private function resolveFormFieldDefinition(FieldDefinition $field, FormInterface $form): void
    {
        $type = null;
        $resolver = $form->getConfig()->getType()->getOptionsResolver();
        if ($resolver->hasDefault('graphql_type')) {
            $type = $resolver->resolve([])['graphql_type'];
            if (!$type) {
                $type = $form->getConfig()->getOptions()['graphql_type'];
            }
            $field->setList(TypeUtil::isTypeList($type));
            $type = TypeUtil::normalize($type);
        }

        if (!$type) {
            $guesses = [];
            foreach ($this->typeGuessers as $guesser) {
                $formType = \get_class($form->getConfig()->getType()->getInnerType());
                if ($guess = $guesser->guessType($field, $formType, $form->getConfig()->getOptions())) {
                    $guesses[] = $guess;
                }
            }

            $guess = Guess::getBestGuess($guesses);
            if ($guess && $guess instanceof TypeGuess) {
                $type = $guess->getType();

                if (isset($guess->getOptions()['required'])) {
                    $field->setNonNull($guess->getOptions()['required']);
                }

                if (isset($guess->getOptions()['list'])) {
                    $field->setList($guess->getOptions()['list']);
                }
            }
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
