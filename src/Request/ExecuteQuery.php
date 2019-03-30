<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Request;

use Ynlo\GraphQLBundle\Subscription\SubscriptionRequest;

class ExecuteQuery
{
    /**
     * A GraphQL language formatted string representing the requested operation.
     *
     * @var string
     */
    protected $requestString = '';

    /**
     * The name of the operation to use if requestString contains multiple
     * possible operations. Can be omitted if requestString contains only
     * one operation.
     *
     * @var string|null
     */
    protected $operationName;

    /**
     * A mapping of variable name to runtime value to use for all variables
     * defined in the requestString.
     *
     * @var array
     */
    protected $variables = [];

    /**
     * @var SubscriptionRequest|null
     */
    protected $subscriptionRequest;

    /**
     * @return string
     */
    public function getRequestString(): string
    {
        return $this->requestString;
    }

    /**
     * @param string $requestString
     *
     * @return ExecuteQuery
     */
    public function setRequestString(string $requestString): ExecuteQuery
    {
        $this->requestString = $requestString;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getOperationName(): ?string
    {
        return $this->operationName;
    }

    /**
     * @param null|string $operationName
     *
     * @return ExecuteQuery
     */
    public function setOperationName(?string $operationName): ExecuteQuery
    {
        $this->operationName = $operationName;

        return $this;
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param array $variables
     *
     * @return ExecuteQuery
     */
    public function setVariables(array $variables): ExecuteQuery
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * @return SubscriptionRequest|null
     */
    public function getSubscriptionRequest(): ?SubscriptionRequest
    {
        return $this->subscriptionRequest;
    }

    /**
     * @param SubscriptionRequest $subscriptionRequest
     *
     * @return ExecuteQuery
     */
    public function setSubscriptionRequest(SubscriptionRequest $subscriptionRequest): ExecuteQuery
    {
        $this->subscriptionRequest = $subscriptionRequest;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->requestString;
    }
}
