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
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class TagSpecification
 */
class TagSpecification
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * TagSpecification constructor.
     *
     * @param string             $id
     * @param string             $name
     * @param array              $attributes
     * @param ContainerInterface $container
     */
    public function __construct($id, $name, array $attributes = [], ContainerInterface $container = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->attributes = $attributes;
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     *
     * @return mixed
     */
    public function getService()
    {
        return $this->container->get($this->getId());
    }
}
