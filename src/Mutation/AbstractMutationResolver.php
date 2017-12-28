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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
abstract class AbstractMutationResolver extends AbstractResolver implements EventSubscriberInterface
{
    /**
     * @param array $input
     *
     * @return mixed
     */
    public function __invoke($input)
    {
        $formBuilder = $this->createDefinitionForm($input);

        $form = null;
        if ($formBuilder) {
            $formBuilder->addEventSubscriber($this);

            $extensionExecutor = function ($method) {
                return function (FormEvent $event) use ($method) {
                    foreach ($this->extensions as $extension) {
                        return call_user_func_array([$extension, $method], [$event]);
                    }
                };
            };

            foreach (self::getSubscribedEvents() as $event => $method) {
                $formBuilder->addEventListener($event, $extensionExecutor($method));
            }

            $form = $formBuilder->getForm();
        }

        if ($form) {
            $form->submit($input, false);
            $data = $form->getData();
        } else {
            $data = $input;
        }

        $violations = new ConstraintViolationList();
        if ($form) {
            $this->extractFormErrors($form, $violations);
        }

        $dryRun = $input['dryRun'] ?? false;

        if ($dryRun) {
            $data = null;
        } else {
            if ((!$form && !$violations->count())
                || ($form->isSubmitted() && $form->isValid() && !$violations->count())
            ) {
                $this->process($data);
            }
        }

        return $this->returnPayload($data, $violations, $input);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
            FormEvents::SUBMIT => 'onSubmit',
            FormEvents::POST_SUBMIT => 'postSubmit',
        ];
    }

    /**
     * @see http://api.symfony.com/4.0/Symfony/Component/Form/FormEvents.html
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
    }

    /**
     * @see http://api.symfony.com/4.0/Symfony/Component/Form/FormEvents.html
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
    }

    /**
     * @see http://api.symfony.com/4.0/Symfony/Component/Form/FormEvents.html
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
    }

    /**
     * @see http://api.symfony.com/4.0/Symfony/Component/Form/FormEvents.html
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
    }

    /**
     * @see http://api.symfony.com/4.0/Symfony/Component/Form/FormEvents.html
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
    }

    /**
     * Actions to process
     * the result processed data is given to payload
     *
     * @param mixed $data
     */
    abstract public function process(&$data);

    /**
     * The payload object or array matching the GraphQL definition
     *
     * @param mixed                   $data        normalized data, its the input data processed by the form
     * @param ConstraintViolationList $violations  violations returned by the form validation process
     * @param array                   $inputSource the original submitted data in array
     *
     * @return mixed
     */
    abstract public function returnPayload($data, ConstraintViolationList $violations, $inputSource);

    /**
     * @param mixed $data
     * @param bool  $disableValidation
     *
     * @return FormBuilderInterface|null
     */
    public function createDefinitionForm($data, $disableValidation = false): ?FormBuilderInterface
    {
        if (!$this->context->getDefinition()->hasMeta('form')) {
            return null;
        }

        $formConfig = $this->context->getDefinition()->getMeta('form') ?? [];
        $formType = $formConfig['type'] ?? null;
        if (!$formConfig || !$formType) {
            throw new \RuntimeException(sprintf('Can`t find a valid form for %s', $this->context->getDefinition()->getName()));
        }

        $options = [
            'allow_extra_fields' => true,
        ];

        if ($this->container->hasParameter('form.type_extension.csrf.enabled')
            && $this->container->getParameter('form.type_extension.csrf.enabled')) {
            $options['csrf_protection'] = false;
        }

        $options = array_merge($options, $formConfig['options'] ?? []);

        if ($disableValidation) {
            $options['validation_groups'] = false;
        }

        $form = $this->createFormBuilder($formType, $data, $options);
        $viewTransformer = new DataWithIdToNodeTransformer($this->getManager(), $this->context->getEndpoint());
        $form->addViewTransformer($viewTransformer);

        return $form;
    }

    /**
     * @param FormInterface           $form
     * @param ConstraintViolationList $violations
     * @param null|string             $parentName
     */
    public function extractFormErrors(FormInterface $form, ConstraintViolationList $violations, ?string $parentName = null)
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
}
