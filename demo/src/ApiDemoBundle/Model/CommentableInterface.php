<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Model;

use Doctrine\Common\Collections\Collection;
use Ynlo\GraphQLBundle\Annotation as API;

/**
 * @API\InterfaceType(description="Accept comments")
 */
interface CommentableInterface
{
    /**
     * @API\Field(type="[Comment]")
     *
     * @return Collection
     */
    public function getComments(): Collection;

    /**
     * @param Collection $comments
     *
     * @return mixed
     */
    public function setComments(Collection $comments);

    /**
     * Each implementor should return the custom implementation
     *
     * @return CommentInterface
     */
    public function createComment(): CommentInterface;
}
