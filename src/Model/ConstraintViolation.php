<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Model;

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Util\IDEncoder;

/**
 * @GraphQL\ObjectType(description="A violation of a constraint that happened during validation.

For each constraint that fails during validation one or more violations are
created. The violations store the violation message, the path to the failing
element in the validation graph and other helpful information,
like template and arguments to use with a translation engine")
 */
class ConstraintViolation
{
    /**
     * @var string
     *
     * @GraphQL\Field(type="string",
     *     description="Returns the property path from the root element to the violation.")
     */
    protected $propertyPath;

    /**
     * @var string
     *
     * @GraphQL\Field(type="string!",
     *     description="Returns the violation message.")
     */
    protected $message;

    /**
     * @var string
     *
     * @GraphQL\Field(type="string!", description="Returns the raw violation message.
    The raw violation message contains placeholders for the parameters returned by parameters.
    Typically you'll pass the message template and parameters to a translation engine.")
     */
    protected $messageTemplate;

    /**
     * @var ConstraintViolationParameter[]
     *
     * @GraphQL\Field(type="[Ynlo\GraphQLBundle\Model\ConstraintViolationParameter]",
     *     description="Returns the parameters to be inserted into the raw violation message.")
     */
    protected $parameters;

    /**
     * @var int
     *
     * @GraphQL\Field(type="int",
     *     description="Returns a number for pluralizing the violation message")
     */
    protected $plural = 0;

    /**
     * @var string
     *
     * @GraphQL\Field(type="string",
     *     description="Returns the value that caused the violation.")
     */
    protected $invalidValue;

    /**
     * @var string
     *
     * @GraphQL\Field(type="string!",
     *     description="Returns a machine-digestible error code for the violation.")
     */
    protected $code;


    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return ConstraintViolation
     */
    public function setMessage(string $message): ConstraintViolation
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getPropertyPath(): ?string
    {
        return $this->propertyPath;
    }

    /**
     * @param string $path
     *
     * @return ConstraintViolation
     */
    public function setPropertyPath(?string $path): ConstraintViolation
    {
        $this->propertyPath = $path;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInvalidValue()
    {
        return $this->invalidValue;
    }

    /**
     * @param mixed $invalidValue
     *
     * @return ConstraintViolation
     */
    public function setInvalidValue($invalidValue): ConstraintViolation
    {
        $this->invalidValue = $this->normalizeValue($invalidValue);

        return $this;
    }

    /**
     * @return ConstraintViolationParameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return ConstraintViolation
     */
    public function addParameter($name, $value): ConstraintViolation
    {
        $this->parameters[] = new ConstraintViolationParameter($name, $this->normalizeValue($value));

        return $this;
    }

    /**
     * @return string
     */
    public function getMessageTemplate(): string
    {
        return $this->messageTemplate;
    }

    /**
     * @param string $messageTemplate
     *
     * @return ConstraintViolation
     */
    public function setMessageTemplate(string $messageTemplate): ConstraintViolation
    {
        $this->messageTemplate = $messageTemplate;

        return $this;
    }

    /**
     * @return int
     */
    public function getPlural(): ?int
    {
        return $this->plural;
    }

    /**
     * @param int $plural
     *
     * @return ConstraintViolation
     */
    public function setPlural(?int $plural): ConstraintViolation
    {
        $this->plural = $plural;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return ConstraintViolation
     */
    public function setCode(string $code): ConstraintViolation
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $params = [];
        foreach ($this->getParameters() as $parameter) {
            $params[$parameter->getName()] = $parameter->getValue();
        }

        return [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            'messageTemplate' => $this->getMessageTemplate(),
            'propertyPath' => $this->getPropertyPath(),
            'parameters' => $params,
            'invalidValue' => $this->getInvalidValue(),
            'plural' => $this->getPlural(),
        ];
    }

    /**
     * @param mixed $value
     *
     * @return null|string|mixed
     */
    private function normalizeValue($value)
    {
        if ($value instanceof NodeInterface) {
            $value = IDEncoder::encode($value);
        } elseif (\is_object($value)) {
            if (method_exists($value, '__toString')) {
                $value = (string) $value;
            } else {
                $value = null;
            }
        }

        return $value;
    }
}
