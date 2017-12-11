<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Extension;

use Ynlo\GraphQLBundle\Component\TaggedServices\TaggedServices;
use Ynlo\GraphQLBundle\Component\TaggedServices\TagSpecification;

/**
 * ExtensionManager
 */
class ExtensionManager
{
    /**
     * @var GraphQLExtensionInterface[]
     */
    protected $extensions;

    /**
     * @var bool
     */
    protected $loaded = false;

    /**
     * @var TaggedServices
     */
    protected $taggedServices;

    /**
     * ExtensionManager constructor.
     *
     * @param TaggedServices $taggedServices
     */
    public function __construct(TaggedServices $taggedServices)
    {
        $this->taggedServices = $taggedServices;
    }

    /**
     * @return array|GraphQLExtensionInterface[]
     */
    public function getExtensions()
    {
        if ($this->loaded) {
            return $this->extensions;

        }
        $this->extensions = [];

        /** @var TagSpecification $extensions */
        $taggedServices = $this->taggedServices->findTaggedServices('graphql.extension');
        foreach ($taggedServices as $tagSpecification) {
            $this->extensions[] = $tagSpecification->getService();
        }

        $this->loaded = true;

        return $this->extensions;
    }
}
