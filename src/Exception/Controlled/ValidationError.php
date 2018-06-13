<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Exception\Controlled;

use Symfony\Component\HttpFoundation\Response;
use Ynlo\GraphQLBundle\Exception\ControlledError;
use Ynlo\GraphQLBundle\Model\ConstraintViolation;
use Ynlo\GraphQLBundle\Validator\ConstraintViolationList;

class ValidationError extends ControlledError
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    protected $message = 'Unprocessable Entity';

    protected $description = 'Validation Error. These errors contains a key "constraintViolations" with all violations.';

    /**
     * @var ConstraintViolationList
     */
    protected $violations;

    /**
     * ValidationError constructor.
     *
     * @param ConstraintViolationList $violations
     */
    public function __construct(ConstraintViolationList $violations)
    {
        $this->violations = $violations;

        parent::__construct();
    }

    /**
     * @return array
     */
    public function getViolationsArray()
    {
        $violations = [];
        /** @var ConstraintViolation $v */
        foreach ($this->violations->all() as $v) {
            $violations[] = $v->toArray();
        }

        return $violations;
    }
}
