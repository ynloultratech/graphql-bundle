<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Tests\Fixtures\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @GraphQL\ObjectType()
 */
class Category implements NodeInterface
{
    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var int|null
     */
    protected $name;

    /**
     * @param int|null $id
     */
    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->id;
    }
}
