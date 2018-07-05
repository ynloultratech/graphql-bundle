<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Model;

use Doctrine\Common\Collections\Collection;
use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * @GraphQL\InterfaceType(description="Accept comments")
 */
interface CommentableInterface
{
    /**
     * @GraphQL\Field(type="[Comment]")
     */
    public function getComments(): Collection;

    /**
     * @return mixed
     */
    public function setComments(Collection $comments);

    /**
     * Each implementor should return the custom implementation
     */
    public function createComment(): CommentableInterface;
}