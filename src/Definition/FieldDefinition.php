<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition;

use Ynlo\GraphQLBundle\Definition\Traits\ExecutableDefinitionTrait;

/**
 * Class FieldDefinition
 */
class FieldDefinition implements ExecutableDefinitionInterface
{
    use ExecutableDefinitionTrait;

    /**
     * @var string
     */
    protected $originName;

    /**
     * @var string
     */
    protected $originType;

    /**
     * @return mixed
     */
    public function getOriginName()
    {
        return $this->originName;
    }

    /**
     * @param mixed $originName
     */
    public function setOriginName($originName)
    {
        $this->originName = $originName;
    }

    /**
     * @return mixed
     */
    public function getOriginType()
    {
        return $this->originType;
    }

    /**
     * @param mixed $originType
     */
    public function setOriginType($originType)
    {
        $this->originType = $originType;
    }
}
