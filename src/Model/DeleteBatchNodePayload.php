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
class DeleteBatchNodePayload
{
    /**
     * @var string[]
     *
     * @API\Field(type="[ID!]!", description="IDs of the node deleted on success")
     */
    public $ids = [];

    /**
     * @var null|string
     *
     * @API\Field(type="string")
     */
    public $clientMutationId;

    /**
     * @param NodeInterface[] $nodes
     * @param null|string     $clientMutationId
     */
    public function __construct($nodes, ?string $clientMutationId = null)
    {
        /** @var NodeInterface $node */
        foreach ($nodes as $node) {
            $this->ids[] = IDEncoder::encode($node);
        }
        $this->clientMutationId = $clientMutationId;
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @return null|string
     */
    public function getClientMutationId()
    {
        return $this->clientMutationId;
    }
}
