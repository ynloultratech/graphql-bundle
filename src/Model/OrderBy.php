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

/**
 * @GraphQL\InputObjectType()
 */
class OrderBy
{
    /**
     * @var string
     *
     * @GraphQL\Field(type="string!")
     */
    protected $field;

    /**
     * @var string
     *
     * @GraphQL\Field(type="string")
     */
    protected $direction = 'ASC';

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     *
     * @return OrderBy
     */
    public function setField(string $field): OrderBy
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     *
     * @return OrderBy
     */
    public function setDirection(string $direction): OrderBy
    {
        $this->direction = $direction;

        return $this;
    }
}
