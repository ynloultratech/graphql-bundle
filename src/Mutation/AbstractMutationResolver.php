<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Mutation;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation as SymfonyConstraintViolation;
use Ynlo\GraphQLBundle\Action\AbstractNodeAction;
use Ynlo\GraphQLBundle\Form\DataTransformer\DataWithIdToNodeTransformer;
use Ynlo\GraphQLBundle\Model\ConstraintViolation;

/**
 * Base class for mutations
 * Implement the method "process()" and "returnPayload()" is enough in many scenarios
 */
abstract class AbstractMutationResolver extends AbstractNodeAction
{
    /**
     * @param array $input
     *
     * @return mixed
     */
    public function __invoke($input)
    {
        $form = $this->createDefinitionForm($input)->getForm();

        $this->preValidate($input);
        $form->submit($input, false);
        $this->postFormSubmit($input, $form->getData());

        $violations = $this->extractFormErrors($form);
        $this->postValidation($input, $violations);

        $data = $form->getData();

        $dryRun = $input['dryRun'] ?? false;

        if ($dryRun) {
            $data = null;
        } else {
            if ($form->isSubmitted() && $form->isValid() && !count($violations)) {
                $this->process($form->getData());
            }
        }

        return $this->returnPayload($data, $violations, $input);
    }

    /**
     * Actions to process
     *
     * @param mixed $data
     */
    abstract protected function process($data);

    /**
     * The payload object or array matching the GraphQL definition
     *
     * @param mixed $data        normalized data, its the input data processed by the form
     * @param array $violations  array of violations returned by the form validation process
     * @param array $inputSource the original submitted data in array
     *
     * @return mixed
     */
    abstract protected function returnPayload($data, $violations, $inputSource);

    /**
     * @param mixed $data
     *
     * @return FormBuilderInterface
     */
    protected function createDefinitionForm($data): FormBuilderInterface
    {
        if (!$this->context->getDefinition()->getMeta('form')) {
            throw new \RuntimeException(sprintf('Can`t find a valid form for %s', $this->context->getDefinition()->getName()));
        }

        $form = $this->context->getDefinition()->getMeta('form');

        $options = [
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ];
        if ($this->context->getDefinition()->hasMeta('form_options')) {
            $options = array_merge($options, $this->context->getDefinition()->getMeta('form_options'));
        }

        $form = $this->createFormBuilder($form, $data, $options);
        $viewTransformer = new DataWithIdToNodeTransformer($this->getManager(), $this->context->getDefinitionManager());
        $form->addViewTransformer($viewTransformer);

        return $form;
    }

    /**
     * @param FormInterface $form
     * @param null|string   $parentName
     *
     * @return array
     */
    protected function extractFormErrors(FormInterface $form, ?string $parentName = null): array
    {
        $violations = [];
        $errors = $form->getErrors();
        foreach ($errors as $error) {
            $violation = new ConstraintViolation();

            $path = null;
            if ($form->getParent()) {
                $path = $form->getName();
            }
            if ($parentName) {
                $path = $parentName.'.'.$form->getName();
            }

            $violation->setPropertyPath($path);
            $violation->setMessage($error->getMessage());
            $violation->setMessageTemplate($error->getMessageTemplate());
            foreach ($error->getMessageParameters() as $key => $value) {
                $violation->addParameter($key, $value);
            }

            $cause = $error->getCause();
            if ($cause instanceof SymfonyConstraintViolation) {
                $violation->setCode($cause->getCode());
                $violation->setInvalidValue($cause->getInvalidValue());
                $violation->setPlural($cause->getPlural());
            }

            $violations[] = $violation;
        }
        if ($form->all()) {
            foreach ($form->all() as $child) {
                $parentName = $form->getName();

                //avoid set the name of the form
                if ($form->getName() === $parentName && !$form->getParent()) {
                    $parentName = null;
                }

                $childViolations = $this->extractFormErrors($child, $parentName);
                $violations = array_merge($violations, $childViolations);
            }
        }

        return $violations;
    }

    /**
     * Can use this method to verify if submitted data is valid
     * otherwise can trow a error
     *
     * @param mixed $inputSource   contain the original submitted input data
     * @param mixed $submittedData contains the processed data by the form
     */
    protected function postFormSubmit($inputSource, $submittedData)
    {
        //override in child
    }

    /**
     * Can do something before validate the submitted data
     *
     * @param mixed $data
     */
    protected function preValidate(&$data)
    {
        //override in child
    }

    /**
     * Can use this to add your custom validations errors
     *
     * @param mixed                       $data
     * @param array|ConstraintViolation[] $violations
     */
    protected function postValidation($data, &$violations)
    {
        //override in child
    }
}
