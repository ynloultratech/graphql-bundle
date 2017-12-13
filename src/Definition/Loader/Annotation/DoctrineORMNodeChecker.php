<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Loader\Annotation;

use Doctrine\ORM\Mapping\Entity;
use Ynlo\GraphQLBundle\Annotation;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * DoctrineORMNodeChecker
 */
class DoctrineORMNodeChecker implements AnnotationParserInterface
{
    use AnnotationReaderAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function supports($annotation): bool
    {
        return $annotation instanceof Entity;
    }

    /**
     * {@inheritDoc}
     */
    public function parse($annotation, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        if ($objectType = $this->reader->getClassAnnotation($refClass, Annotation\ObjectType::class)) {
            if (!$refClass->implementsInterface(NodeInterface::class)) {
                $error = sprintf('Invalid object "%s". All entities used as GraphQL object must implements %s', $refClass->getName(), NodeInterface::class);
                throw new \LogicException($error);
            }
        }
    }
}
