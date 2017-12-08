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
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;
use Ynlo\GraphQLBundle\Form\Type\IDType;

/**
 * Resolve queries
 */
class MutationDefinitionLoader implements DefinitionResolverInterface
{
    use AnnotationReaderAwareTrait;
    use ObjectQueryTrait;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var DefinitionManager
     */
    protected $definitionManager;

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
    public function resolve($annotation, \ReflectionClass $refClass, DefinitionManager $definitionManager)
    {
        $this->definitionManager = $definitionManager;
        $mutation = $this->createMutation($annotation);
        $this->definitionManager->addMutation($mutation);
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

        $this->definitionManager->addType($inputObject);

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
                $this->definitionManager->addType($child);
                $field->setType($child->getName());
            } else {
                $field->setType($this->getFormType($formField));
            }

            $inputObject->addField($field);
        }

        return $inputObject;
    }

    /**
     * @param FormInterface $form
     *
     * @return string
     */
    public function getFormType(FormInterface $form)
    {
        $resolver = $form->getConfig()->getType()->getOptionsResolver();
        if ($resolver->hasDefault('graphql_type')) {
            return $resolver->resolve([])['graphql_type'];
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), IDType::class, true)) {
            return Type::ID;
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), TextType::class, true)) {
            return Type::STRING;
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), EmailType::class, true)) {
            return Type::STRING;
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), CheckboxType::class, true)) {
            return Type::BOOLEAN;
        }

        if (is_a($form->getConfig()->getType()->getInnerType(), IntegerType::class, true)) {
            return Type::INT;
        }
    }
}
