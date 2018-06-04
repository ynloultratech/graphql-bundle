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

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Util\IDEncoder;

/**
 * Class IDToNodeTransformer
 */
class IDToNodeTransformer implements DataTransformerInterface
{
    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param NodeInterface|NodeInterface[] $node
     *
     * @return string|string[]
     */
    public function transform($node)
    {
        if (!$node) {
            return $node;
        }

        if (\is_array($node) || $node instanceof \Traversable) {
            $ids = [];
            /** @var array $node */
            foreach ($node as $n) {
                $ids[] = $this->transform($n);
            }

            return $ids;
        }

        return IDEncoder::encode($node);
    }

    /**
     * Transforms a string (id) to an object (node).
     *
     * @param string|string[]|mixed $globalId
     *
     * @return mixed
     *
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($globalId)
    {
        if (!$globalId || \is_object($globalId)) {
            return $globalId;
        }

        if (\is_array($globalId)) {
            $nodes = [];
            /** @var array $globalId */
            foreach ($globalId as $id) {
                $nodes[] = $this->reverseTransform($id);
            }

            return $nodes;
        }

        $node = IDEncoder::decode($globalId);

        if (null === $node) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(
                sprintf(
                    'An node with id "%s" does not exist!',
                    $globalId
                )
            );
        }

        return $node;
    }
}
