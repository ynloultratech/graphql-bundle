<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Validator;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Ynlo\GraphQLBundle\Definition\FieldsAwareDefinitionInterface;
use Ynlo\GraphQLBundle\DefinitionLoader\DefinitionManager;
use Ynlo\GraphQLBundle\Model\ConstraintViolation;

/**
 * Convert symfony validations to custom validation system
 * using public exposed paths in the API
 */
class ValidatorBridge
{
    /**
     * @var DefinitionManager
     */
    protected $definitionManager;

    /**
     * ValidatorBridge constructor.
     *
     * @param DefinitionManager $definitionManager
     */
    public function __construct(DefinitionManager $definitionManager)
    {
        $this->definitionManager = $definitionManager;
    }

    /**
     * Convert symfony violation list to internal violation list to use in responses
     * also convert internal object path to exposed GraphQL object fields
     *
     * @param ConstraintViolationListInterface $violations
     * @param FieldsAwareDefinitionInterface   $contextDefinition
     *
     * @return ConstraintViolation[]
     */
    public function convertViolations(ConstraintViolationListInterface $violations, FieldsAwareDefinitionInterface $contextDefinition): array
    {
        $normalizedViolations = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $publicPath = null;
            if ($validatorPath = $violation->getPropertyPath()) {
                $publicPath = $this->resolvePublicPropertyPath($contextDefinition, $validatorPath);
            }

            $normalizedViolation = new ConstraintViolation();
            $normalizedViolation->setMessage($violation->getMessage());
            $normalizedViolation->setInvalidValue($violation->getInvalidValue());
            $normalizedViolation->setMessageTemplate($violation->getMessageTemplate());
            $normalizedViolation->setCode($violation->getCode());
            $normalizedViolation->setPlural($violation->getPlural());
            $normalizedViolation->setPropertyPath($publicPath);
            foreach ($violation->getParameters() as $name => $value) {
                $normalizedViolation->addParameter($name, $value);
            }
            $normalizedViolations[] = $normalizedViolation;
        }

        return $normalizedViolations;
    }

    /**
     * @param FieldsAwareDefinitionInterface $definition
     * @param string                         $validatorPath
     *
     * @return string
     */
    private function resolvePublicPropertyPath(FieldsAwareDefinitionInterface $definition, $validatorPath)
    {
        if (strpos($validatorPath, '.') === false) {
            $pathArray = [$validatorPath];
        } else {
            $pathArray = explode('.', $validatorPath);
        }
        foreach ($pathArray as &$path) {
            if (!$definition->hasField($path)) {
                foreach ($definition->getFields() as $field) {
                    if ($path === $field->getOriginName()) {
                        $path = $field->getName();
                    }
                }
            }
        }
        unset($path);

        return implode('.', $pathArray);
    }
}
