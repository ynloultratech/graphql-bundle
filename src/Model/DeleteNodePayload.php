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
use Ynlo\GraphQLBundle\Util\IDEncoder;

/**
 * @API\ObjectType()
 */
class DeleteNodePayload
{
    /**
     * @var string
     *
     * @API\Field(type="ID!", description="ID of the node deleted on success")
     */
    public $id;

    /**
     * @var null|string
     *
     * @API\Field(type="string")
     */
    public $clientMutationId;

    /**
     *
     * @param NodeInterface $node
     * @param null|string   $clientMutationId
     */
    public function __construct(NodeInterface $node, ?string $clientMutationId = null)
    {
        $this->id = IDEncoder::encode($node);
        $this->clientMutationId = $clientMutationId;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getClientMutationId()
    {
        return $this->clientMutationId;
    }
}
