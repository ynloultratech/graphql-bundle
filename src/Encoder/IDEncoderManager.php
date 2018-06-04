<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Encoder;

class IDEncoderManager
{
    /**
     * @var IDEncoderInterface
     */
    protected $encoder;

    /**
     * IDEncoderManager constructor.
     *
     * @param IDEncoderInterface $encoder
     */
    public function __construct(IDEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @return IDEncoderInterface
     */
    public function getEncoder(): IDEncoderInterface
    {
        return $this->encoder;
    }
}
