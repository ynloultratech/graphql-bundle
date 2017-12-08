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
class RemoveNodePayload
{
    /**
     * @var ID
     *
     * @API\Field(type="ID!", description="ID of the node removed on success")
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
     * @param ID          $id
     * @param null|string $clientMutationId
     */
    public function __construct(ID $id, ?string $clientMutationId = null)
    {
        $this->id = $id;
        $this->clientMutationId = $clientMutationId;
    }

    /**
     * @return ID
     */
    public function getId(): ID
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
