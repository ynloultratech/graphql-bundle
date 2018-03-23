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
class DeleteBatchNodePayload
{
    /**
     * @var ID
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
     *
     * @param ID[]        $ids
     * @param null|string $clientMutationId
     */
    public function __construct(array $ids, ?string $clientMutationId = null)
    {
        $this->ids = $ids;
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
