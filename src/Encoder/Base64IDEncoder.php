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

use Ynlo\GraphQLBundle\Model\NodeInterface;

class Base64IDEncoder extends SimpleIDEncoder
{
    /**
     * {@inheritDoc}
     */
    public function encode(NodeInterface $node): ?string
    {
        return base64_encode(parent::encode($node));
    }

    /**
     * {@inheritDoc}
     */
    public function decode($globalId): ?NodeInterface
    {
        return parent::decode(base64_encode($globalId));
    }
}
