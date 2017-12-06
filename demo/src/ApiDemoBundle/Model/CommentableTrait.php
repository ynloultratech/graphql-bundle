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
use Ynlo\GraphQLBundle\Demo\ApiDemoBundle\Entity\PostComment;

/**
 * Trait CommentableTrait
 */
trait CommentableTrait
{
    /**
     * @var Collection|PostComment[]
     */
    protected $comments;

    /**
     * {@inheritdoc}
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * {@inheritdoc}
     */
    public function setComments(Collection $comments)
    {
        $this->comments = $comments;
    }
}
