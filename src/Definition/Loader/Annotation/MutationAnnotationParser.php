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

use GraphQL\Type\Definition\Type;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\ArgumentDefinition;
use Ynlo\GraphQLBundle\Definition\FieldDefinition;
use Ynlo\GraphQLBundle\Definition\InputObjectDefinition;
use Ynlo\GraphQLBundle\Definition\MutationDefinition;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Form\Type\GraphQLType;
use Ynlo\GraphQLBundle\Form\Type\IDType;

/**
 * Parse mutation annotation to fetch definitions
 */
class MutationAnnotationParser implements AnnotationParserInterface
{
    use AnnotationReaderAwareTrait;
    use AnnotationParserHelper;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * @param FormFactory $formFactory
     */
    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($annotation): bool
    {
        return get_class($annotation) === Annotation\Mutation::class;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        /** @var Annotation\Mutation $annotation */

        if (!$annotation->name) {
            $annotation->name = $this->getDefaultName($refClass, $endpoint);
        }

        //try find form using naming convention
        //the form should be placed in the same bundle with the same name of the mutation with "Input" suffix
        //e.g. AppBundle\Mutation\User\AddUser => AppBundle\Form\Input\User\AddUserInput
        if (!$annotation->form) {
            $definition = $this->getObjectDefinition($refClass, $endpoint);
            if ($class = $definition->getClass()) {
                $bundleNamespace = preg_replace('~Bundle(?!.*Bundle)[\\\\\w+]+~', null, $class).'Bundle';
                $formClass = sprintf('%s\Form\Input\%s\%sInput', $bundleNamespace, $definition->getName(), ucfirst($annotation->name));
                if (class_exists($formClass)) {
                    $annotation->form = $formClass;
                } else {
                    $error = sprintf(
                        'Can`t find a valid input form type to use in "%s".
                         Create the form "%s" or specify a custom form in the annotation of "%s"',
                        $annotation->name,
                        $formClass,
                        $refClass->getName()
                    );
                    throw new \Exception($error);
                }
            }
        }

        $this->endpoint = $endpoint;
        $mutation = $this->createMutation($annotation);

        if (!$mutation->hasMeta('node')) {
            if (isset($definition)) {
                $mutation->setMeta('node', $definition->getName());
            }

            if ($mutation->hasMeta('form_options')) {
                $options = $mutation->getMeta('form_options');
                $class = $options['data_class'] ?? '';
                if ($endpoint->hasTypeForClass($class)) {
                    $mutation->setMeta('node', $endpoint->getTypeForClass($class));
                }
            }
        }

        if (!$mutation->getType()) {
            $mutation->setType($annotation->payload);
        }

        if (!$mutation->getType()) {
            $error = sprintf(
                'The mutation "%s" does not have a valid payload, must define the payload in the annotation.',
                $annotation->name
            );
            throw new \Exception($error);
        }

        if (!$mutation->getResolver()) {
            $mutation->setResolver($refClass->getName());
        }

        $this->endpoint->addMutation($mutation);
    }

    /**
     * @param Annotation\Mutation $annotation
     *
     * @return MutationDefinition
     */
    public function createMutation(Annotation\Mutation $annotation): MutationDefinition
    {
        $mutation = new MutationDefinition();
        $mutation->setName($annotation->name);
        $mutation->setDescription($annotation->description);
        $mutation->setDeprecationReason($annotation->deprecationReason);

        $formType = $annotation->form;
        $form = $this->formFactory->create($formType, null, $annotation->formOptions);

        $inputObject = $this->createFormInputObject($form, ucfirst($mutation->getName()));

        if ($annotation->clientMutationId) {
            $clientMutationId = new FieldDefinition();
            $clientMutationId->setName('clientMutationId');
            $clientMutationId->setType(Type::STRING);
            $clientMutationId->setDescription('A unique identifier for the client performing the mutation.');
            $inputObject->prependField($clientMutationId);
        }

        if ($annotation->dryRun) {
            $clientMutationId = new FieldDefinition();
            $clientMutationId->setName('dryRun');
            $clientMutationId->setType(Type::BOOLEAN);
            $clientMutationId->setDescription(
                'Execute only a validation process without save anything.
Helpful to create a server side validation. 
Must check `constraintViolations` in the payload to get validation messages.'
            );
            $inputObject->prependField($clientMutationId);
        }

        $this->endpoint->addType($inputObject);

        $input = new ArgumentDefinition();
        $input->setName('input');
        $input->setType($inputObject->getName());

        $mutation->addArgument($input);
        $mutation->setMeta('form', $formType);
        $mutation->setMeta('form_options', $annotation->formOptions);

        return $mutation;
    }

    /**
     * @param FormInterface $form
     * @param string        $name
     *
     * @return InputObjectDefinition
     */
    public function createFormInputObject(FormInterface $form, $name)
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
                $child = $this->createFormInputObject($formField, $childName);
                $this->endpoint->addType($child);
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
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), GraphQLType::class, true)) {
            $type = $form->getConfig()->getOptions()['graphql_type'];
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), IDType::class, true)) {
            if ($form->getConfig()->hasOption('multiple') && $form->getConfig()->getOption('multiple')) {
                $field->setList(true);
            }
            $type = Type::ID;
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), TextType::class, true)) {
            $type = Type::STRING;
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), EmailType::class, true)) {
            $type = Type::STRING;
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), CheckboxType::class, true)) {
            $type = Type::BOOLEAN;
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), IntegerType::class, true)) {
            $type = Type::INT;
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
