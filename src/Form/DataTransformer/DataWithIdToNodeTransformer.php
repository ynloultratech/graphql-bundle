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
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionManager;
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
     * {@inheritdoc}
     */
    public function transform($data)
    {
        if (is_array($data) && isset($data['id'])) {
            $id = ID::createFromString($data['id']);
            if ($this->dm->hasType($id->getNodeType())) {
                $class = $this->dm->getClassForType($id->getNodeType());
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
