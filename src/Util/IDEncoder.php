<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Util;

use Ynlo\GraphQLBundle\Encoder\IDEncoderInterface;
use Ynlo\GraphQLBundle\Model\NodeInterface;

abstract class IDEncoder
{
    /**
     * @var IDEncoderInterface
     */
    private static $encoder;

    public static function setup(IDEncoderInterface $encoder)
    {
        self::$encoder = $encoder;
    }

    /**
     * {@inheritDoc}
     */
    public static function encode(NodeInterface $node): ?string
    {
        return static::$encoder->encode($node);
    }

    /**
     * {@inheritDoc}
     */
    public static function decode($globalId): ?NodeInterface
    {
        return static::$encoder->decode($globalId);
    }
}
