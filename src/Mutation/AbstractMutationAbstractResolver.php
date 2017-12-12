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

use Mockery\Exception;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation as SymfonyConstraintViolation;
use Ynlo\GraphQLBundle\Form\DataTransformer\DataWithIdToNodeTransformer;
use Ynlo\GraphQLBundle\Model\ConstraintViolation;
use Ynlo\GraphQLBundle\Resolver\AbstractResolver;
use Ynlo\GraphQLBundle\Validator\ConstraintViolationList;

/**
 * Base class for mutations
 * Implement the method "process()" and "returnPayload()" is enough in many scenarios
 */
abstract class AbstractMutationAbstractResolver extends AbstractResolver
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

        $data = $form->getData();
        $this->onSubmit($input, $data);

        $violations = new ConstraintViolationList();
        $this->extractFormErrors($form, $violations);
        $this->postValidation($data, $violations);

        $dryRun = $input['dryRun'] ?? false;

        if ($dryRun) {
            $data = null;
        } else {
            if ($form->isSubmitted() && $form->isValid() && !$violations->count()) {
                $this->process($data);
            }
        }

        return $this->returnPayload($data, $violations, $input);
    }

    /**
     * Actions to process
     * the result processed data is given to payload
     *
     * @param mixed $data
     */
    abstract protected function process(&$data);

    /**
     * The payload object or array matching the GraphQL definition
     *
     * @param mixed                   $data        normalized data, its the input data processed by the form
     * @param ConstraintViolationList $violations  violations returned by the form validation process
     * @param array                   $inputSource the original submitted data in array
     *
     * @return mixed
     */
    abstract protected function returnPayload($data, ConstraintViolationList $violations, $inputSource);

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
        $viewTransformer = new DataWithIdToNodeTransformer($this->getManager(), $this->context->getEndpoint());
        $form->addViewTransformer($viewTransformer);

        return $form;
    }

    /**
     * @param FormInterface           $form
     * @param ConstraintViolationList $violations
     * @param null|string             $parentName
     */
    protected function extractFormErrors(FormInterface $form, ConstraintViolationList $violations, ?string $parentName = null)
    {
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

            $violations->addViolation($violation);
        }
        if ($form->all()) {
            foreach ($form->all() as $child) {
                $parentName = $form->getName();

                //avoid set the name of the form
                if ($form->getName() === $parentName && !$form->getParent()) {
                    $parentName = null;
                }

                $this->extractFormErrors($child, $violations, $parentName);
            }
        }
    }

    /**
     * Can use this method to verify if submitted data is valid
     * otherwise can trow a error
     *
     * @param mixed $inputSource contain the original submitted input data
     * @param mixed $normData    contains the processed and normalized data by the form
     */
    protected function onSubmit($inputSource, &$normData)
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
     * @param mixed                   $data
     * @param ConstraintViolationList $violations
     */
    protected function postValidation($data, ConstraintViolationList $violations)
    {
        //override in child
    }
}
