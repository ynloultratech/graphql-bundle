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

use Doctrine\Bundle\DoctrineBundle\Registry;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Model\NodeInterface;

class SecureIDEncoder extends SimpleIDEncoder
{
    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $nonce;

    /**
     * @var string
     */
    protected $cipher = "aes-128-ctr";

    /**
     * @param DefinitionRegistry $definitionRegistry
     * @param Registry           $registry
     * @param string             $secret
     */
    public function __construct(DefinitionRegistry $definitionRegistry, Registry $registry, $secret)
    {
        $this->secret = $secret;
        $this->nonce = mb_strcut($secret, 0, 16);

        parent::__construct($definitionRegistry, $registry);
    }

    /**
     * {@inheritDoc}
     */
    public function encode(NodeInterface $node): ?string
    {
        $nodeString = parent::encode($node);
        $ciphertext = openssl_encrypt(
            $nodeString,
            $this->cipher,
            $this->secret,
            OPENSSL_ZERO_PADDING,
            $this->nonce
        );

        return $ciphertext;
    }

    /**
     * {@inheritDoc}
     */
    public function decode($globalId): ?NodeInterface
    {
        $decodedGlobalId = openssl_decrypt(
            $globalId,
            $this->cipher,
            $this->secret,
            OPENSSL_ZERO_PADDING,
            $this->nonce
        );

        return parent::decode($decodedGlobalId);
    }
}
