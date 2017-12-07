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
class UpdateNodePayload
{
    public const TYPE = 'UpdateNodePayload';

    /**
     * @var mixed
     *
     * @API\Field(type="Ynlo\GraphQLBundle\Model\NodeInterface", description="Updated node instance")
     */
    public $node;

    /**
     * @var null|string
     *
     * @API\Field(type="string", description="Unique client mutation identifier")
     */
    public $clientMutationId;

    /**
     * @var ConstraintViolation[]
     *
     * @API\Field(type="[Ynlo\GraphQLBundle\Model\ConstraintViolation]", description="List of `ConstraintViolation` if the validation fails.")
     */
    public $constraintViolations = [];

    /**
     * UpdateNodePayload constructor.
     *
     * @param NodeInterface|null    $node
     * @param ConstraintViolation[] $violations
     * @param null|string           $clientMutationId
     */
    public function __construct(?NodeInterface $node, array $violations = [], ?string $clientMutationId = null)
    {
        $this->node = $node;
        $this->clientMutationId = $clientMutationId;
        $this->constraintViolations = $violations;
    }

    /**
     * @return mixed
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return null|string
     */
    public function getClientMutationId():?string
    {
        return $this->clientMutationId;
    }

    /**
     * @return ConstraintViolation[]
     */
    public function getConstraintViolations(): array
    {
        return $this->constraintViolations;
    }
}
