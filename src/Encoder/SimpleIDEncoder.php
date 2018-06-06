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
use Doctrine\ORM\EntityManagerInterface;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Error\NodeNotFoundException;
use Ynlo\GraphQLBundle\Model\NodeInterface;
use Ynlo\GraphQLBundle\Util\TypeUtil;

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
        $nodeType = TypeUtil::resolveObjectType($this->definitionRegistry->getEndpoint(), $node);

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
                $reference = $manager->getReference($class, $databaseId);
                $resolvedType = TypeUtil::resolveObjectType($this->definitionRegistry->getEndpoint(), $reference);

                //compare the given type encoded in the globalID with the type resolved by the object instance
                //This is important to avoid get a node using different type when use the same entity class
                //e.g. 'AdminUser:1' => resolve to type 'AdminUser' and should not be possible get using 'CommonUser:1' as globalId
                if ($resolvedType !== $nodeType) {
                    return null;
                }

                return $reference;
            }

            throw new \UnexpectedValueException('Not supported doctrine manager.');
        }

        return null;
    }
}
