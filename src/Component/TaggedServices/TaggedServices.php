<?php

/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Component\TaggedServices;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Ynlo\GraphQLBundle\Definition\Loader\Annotation\FieldDecorator\FieldDefinitionDecoratorInterface;

/**
 * Class TaggedServices
 */
class TaggedServices
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $servicesByTags = [];

    /**
     * TaggedServices constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $id
     * @param string $tagName
     * @param array  $tagAttributes
     *
     * @return TaggedServices
     */
    public function addSpecification($id, $tagName, array $tagAttributes = []): TaggedServices
    {
        $this->servicesByTags[$tagName][] = new TagSpecification($id, $tagName, $tagAttributes, $this->container);

        return $this;
    }

    /**
     * findTaggedServices.
     *
     * @param string  $tag
     * @param boolean $orderByPriority
     *
     * @return array|TagSpecification[]
     */
    public function findTaggedServices($tag, $orderByPriority = true)
    {
        $services = [];
        if (array_key_exists($tag, $this->servicesByTags)) {
            $services = $this->servicesByTags[$tag];
        }

        if ($orderByPriority) {
            $this->sortByPriority($services);
        }

        return $services;
    }

    /**
     * @param TagSpecification[] $tagSpecifications
     */
    private function sortByPriority(&$tagSpecifications)
    {
        $orderedSpecifications = [];
        foreach ($tagSpecifications as $tagSpecification) {
            $attr = $tagSpecification->getAttributes();
            $priority = 0;
            if (isset($attr['priority'])) {
                $priority = $attr['priority'];
            }

            $orderedSpecifications[] = [$priority, $tagSpecification];
        }

        //sort by priority
        usort(
            $orderedSpecifications,
            function ($tagSpecification1, $tagSpecification2) {
                list($priority1) = $tagSpecification1;
                list($priority2) = $tagSpecification2;

                return version_compare($priority2 + 250, $priority1 + 250);
            }
        );

        $tagSpecifications = array_column($orderedSpecifications, 1);
    }
}
