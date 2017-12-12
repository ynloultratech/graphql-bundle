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

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\ID;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * Class IDToNodeTransformer
 */
class IDToNodeTransformer implements DataTransformerInterface
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
     * Transforms an object (issue) to a string (number).
     *
     * @param  NodeInterface|null $node
     *
     * @return string
     */
    public function transform($node)
    {
        if (null === $node) {
            return '';
        }

        $nodeType = $this->endpoint->getTypeForClass(ClassUtils::getRealClass($node));

        return ID::encode($nodeType, $node->getId());
    }

    /**
     * Transforms a string (id) to an object (node).
     *
     * @param  string $globalId
     *
     * @return NodeInterface|null
     *
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($globalId)
    {
        if (!$globalId) {
            return null;
        }

        $id = ID::createFromString($globalId);

        if (!$id || !$id->getNodeType() || !$this->endpoint->hasType($id->getNodeType())) {
            return null;
        }

        $node = $this->em
            ->getRepository($this->endpoint->getClassForType($id->getNodeType()))
            ->find($id->getDatabaseId());

        if (null === $node) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(
                sprintf(
                    'An node with id "%s" does not exist!',
                    $id
                )
            );
        }

        return $node;
    }
}
