<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\AppBundle\Model;

use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * Use this interface in all entities that you need to automatically
 * set a timestamp on creation or update.
 *
 * @GraphQL\InterfaceType()
 */
interface TimestampableInterface
{
    /**
     * Get creation time.
     *
     * @return \DateTime
     *
     * @GraphQL\Field(type="datetime!")
     */
    public function getCreatedAt(): \DateTime;

    /**
     * Get the time of last update.
     *
     * @return \DateTime
     *
     * @GraphQL\Field(type="datetime!")
     */
    public function getUpdatedAt(): \DateTime;

    /**
     * Set creation time.
     *
     * @param \DateTime $createdAt
     *
     * @return TimestampableInterface
     */
    public function setCreatedAt(\DateTime $createdAt): TimestampableInterface;

    /**
     * Set the time of last update.
     *
     * @param \DateTime $updatedAt
     *
     * @return TimestampableInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt): TimestampableInterface;
}
