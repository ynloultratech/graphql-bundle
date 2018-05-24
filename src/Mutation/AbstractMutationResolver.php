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
use Ynlo\GraphQLBundle\Model\ConstraintViolation;
use Ynlo\GraphQLBundle\Model\ID;
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
        $formBuilder = $this->createDefinitionForm($this->initialFormData($input));

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
     * @param array $input
     *
     * @return mixed
     */
    public function initialFormData($input)
    {
        if (is_array($input) && isset($input['id'])) {
            $id = ID::createFromString($input['id']);
            if ($this->context->getEndpoint()->hasType($id->getNodeType())) {
                $class = $this->context->getEndpoint()->getClassForType($id->getNodeType());
                if ($class) {
                    return $this->getManager()->getRepository($class)->find($id->getDatabaseId());
                }
            }
        }

        return null;
    }
    /**
     * @param mixed|null $data
     *
     * @return FormBuilderInterface|null
     */
    public function createDefinitionForm($data): ?FormBuilderInterface
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

        return $this->createFormBuilder($formType, $data, $options);
    }

    /**
     * @param FormInterface           $form
     * @param ConstraintViolationList $violations
     * @param null|string             $parentName
     */
    public function extractFormErrors(FormInterface $form, ConstraintViolationList $violations, ?string $parentName = null)
    {
        $errors = $form->getErrors(true);
        foreach ($errors as $error) {
            $violation = new ConstraintViolation();
            $violation->setMessage($error->getMessage());
            $violation->setMessageTemplate($error->getMessageTemplate() ?? $error->getMessage());
            foreach ($error->getMessageParameters() as $key => $value) {
                $violation->addParameter($key, $value);
            }

            $cause = $error->getCause();
            if ($cause instanceof SymfonyConstraintViolation) {
                $violation->setCode($cause->getCode() ?? md5($violation->getMessageTemplate()));
                $violation->setInvalidValue($cause->getInvalidValue());
                $violation->setPlural($cause->getPlural());

                $path = $this->publicPropertyPath($form, $cause->getPropertyPath());
                if ($path) {
                    $violation->setPropertyPath($path);
                }
            }

            $violations->addViolation($violation);
        }
    }

    /**
     * Convert internal validation property path to the public one,
     * required when use `property_path` in the form
     *
     * @param FormInterface $form
     * @param string        $path
     *
     * @return string
     */
    private function publicPropertyPath(FormInterface $form, $path)
    {
        if (strpos($path, '.') !== false) { // object.child.property
            $pathArray = explode('.', $path);
        } elseif (strpos($path, '[') !== false) { //[array][child][property]
            $path = str_replace(']', null, $path);
            $pathArray = explode('[', $path);
        } else {
            $pathArray = [$path];
        }
        if (in_array($pathArray[0], ['data', 'children'])) {
            array_shift($pathArray);
        }

        $contextForm = $form;
        foreach ($pathArray as &$propName) {
            $index = null;
            if (preg_match('/(\w+)(\[\d+\])$/', $propName, $matches)) {
                list(, $propName, $index) = $matches;
            }
            if (!$contextForm->has($propName)) {
                foreach ($contextForm->all() as $child) {
                    if ($child->getConfig()->getOption('property_path') === $propName) {
                        $propName = $child->getName();
                    }
                }
            }
            if ($index) {
                $propName = sprintf('%s%s', $propName, $index);
            }
        }
        unset($propName);

        return implode('.', $pathArray);
    }
}
