<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Encoder;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Model\NodeInterface;

class SimpleIDEncoder implements IDEncoderInterface
{
    private const DIVIDER = ':';

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var DefinitionRegistry
     */
    protected $definitionRegistry;


    public function __construct(DefinitionRegistry $definitionRegistry, Registry $registry)
    {
        $this->doctrine = $registry;
        $this->definitionRegistry = $definitionRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function encode(NodeInterface $node): ?string
    {
        $class = ClassUtils::getClass($node);
        $nodeType = $this->definitionRegistry->getEndpoint()->getTypeForClass($class);

        return sprintf('%s%s%s', $nodeType, self::DIVIDER, $node->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function decode($globalId): ?NodeInterface
    {
        if (strpos($globalId, self::DIVIDER) > 1) {
            list($nodeType, $databaseId) = explode(self::DIVIDER, $globalId);

            $class = $this->definitionRegistry->getEndpoint()->getClassForType($nodeType);
            $manager = $this->doctrine->getManager();
            if ($manager instanceof EntityManagerInterface) {
                return $manager->getReference($class, $databaseId);
            }

            throw new \UnexpectedValueException('Not supported doctrine manager.');
        }

        return null;
    }
}
