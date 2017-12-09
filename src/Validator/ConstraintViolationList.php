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
use Ynlo\GraphQLBundle\Model\ConstraintViolation;

/**
 * ConstraintViolationList
 */
class ConstraintViolationList
{
    /**
     * @var array
     */
    protected $violations = [];

    /**
     * @param ConstraintViolationInterface|ConstraintViolation $constraintViolation
     */
    public function addViolation($constraintViolation)
    {
        if ($constraintViolation instanceof ConstraintViolationInterface) {
            $internalViolation = new ConstraintViolation();
            $internalViolation->setPropertyPath($constraintViolation->getPropertyPath());
            $internalViolation->setInvalidValue($constraintViolation->getInvalidValue());
            $internalViolation->setMessageTemplate($constraintViolation->getMessageTemplate());
            $internalViolation->setMessage($constraintViolation->getMessage());
            $internalViolation->setCode($constraintViolation->getCode());
            $internalViolation->setPlural($constraintViolation->getPlural());
            $this->violations[] = $internalViolation;
        } else {
            $this->violations[] = $constraintViolation;
        }
    }

    /**
     * @param ConstraintViolationListInterface $violations
     */
    public function addViolationList(ConstraintViolationListInterface $violations)
    {
        foreach ($violations as $violation) {
            $this->addViolation($violation);
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->violations);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->violations;
    }
}
