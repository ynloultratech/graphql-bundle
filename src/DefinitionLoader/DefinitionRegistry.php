<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\DefinitionLoader;

use Ynlo\GraphQLBundle\Component\TaggedServices\TaggedServices;
use Ynlo\GraphQLBundle\Component\TaggedServices\TagSpecification;

/**
 * Class DefinitionRegistry
 */
class DefinitionRegistry
{
    /**
     * @var TaggedServices
     */
    private $taggedServices;

    /**
     * @var DefinitionManager[]
     */
    private static $manager = [];

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * DefinitionRegistry constructor.
     *
     * @param TaggedServices $taggedServices
     * @param null|string    $cacheDir
     */
    public function __construct(TaggedServices $taggedServices, ?string $cacheDir = null)
    {
        $this->taggedServices = $taggedServices;
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param string $name
     *
     * @return DefinitionManager
     */
    public function getManager($name = 'default'): DefinitionManager
    {
        if (array_key_exists($name, self::$manager)) {
            return self::$manager[$name];
        }

        self::$manager[$name] = new DefinitionManager();

        $specifications = $this->getTaggedServices('graphql.definition_loader');
        foreach ($specifications as $specification) {
            $resolver = $specification->getService();
            if ($resolver instanceof DefinitionLoaderInterface) {
                $resolver->loadDefinitions(self::$manager[$name]);
            }
        }

        return self::$manager[$name];
    }

    /**
     * @param string $tag
     *
     * @return array|TagSpecification[]
     */
    private function getTaggedServices($tag): array
    {
        return $this->taggedServices->findTaggedServices($tag);
    }
}
