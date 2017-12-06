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

use Ynlo\GraphQLBundle\Annotation as API;

/**
 * @API\ObjectType()
 */
class ConstraintViolationParameter
{
    /**
     * @var string
     *
     * @API\Field(type="string!",
     *     description="Parameter name to use as placeholder in ConstraintViolation.messageTemplate")
     */
    protected $name;

    /**
     * @var string
     *
     * @API\Field(type="string!",
     *     description="Parameter value to replace")
     */
    protected $value;

    /**
     * ConstraintViolationParameter constructor.
     *
     * @param string $name
     * @param string $value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
