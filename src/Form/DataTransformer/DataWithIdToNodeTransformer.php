<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\ID;

/**
 * Class IDToNodeTransformer
 */
class DataWithIdToNodeTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * IDToNodeTransformer constructor.
     *
     * @param EntityManagerInterface $em
     * @param Endpoint               $endpoint
     */
    public function __construct(EntityManagerInterface $em, Endpoint $endpoint)
    {
        $this->em = $em;
        $this->endpoint = $endpoint;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data)
    {
        if (is_array($data) && isset($data['id'])) {
            $id = ID::createFromString($data['id']);
            if ($this->endpoint->hasType($id->getNodeType())) {
                $class = $this->endpoint->getClassForType($id->getNodeType());
                if ($class) {
                    return $this->em->getRepository($class)->find($id->getDatabaseId());
                }
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($data)
    {
        return $data;
    }
}
