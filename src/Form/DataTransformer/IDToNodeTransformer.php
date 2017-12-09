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
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionManager;
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
     * @var DefinitionManager
     */
    protected $dm;

    /**
     * IDToNodeTransformer constructor.
     *
     * @param EntityManagerInterface $em
     * @param DefinitionManager      $definitionManager
     */
    public function __construct(EntityManagerInterface $em, DefinitionManager $definitionManager)
    {
        $this->em = $em;
        $this->dm = $definitionManager;
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

        $nodeType = $this->dm->getTypeForClass(ClassUtils::getRealClass($node));

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

        if (!$id || !$id->getNodeType() || !$this->dm->hasType($id->getNodeType())) {
            return null;
        }

        $node = $this->em
            ->getRepository($this->dm->getClassForType($id->getNodeType()))
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
