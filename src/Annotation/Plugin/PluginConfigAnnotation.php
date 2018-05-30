<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Annotation\Plugin;

use Doctrine\Common\Util\Inflector;

/**
 * Can use this annotation as base to se plugin options.
 * Override and define all plugin settings.
 */
abstract class PluginConfigAnnotation
{
    public function __construct(array $config = [])
    {
        //if only one value is given, the first property is set with the given value
        if (isset($config['value']) && count($config) === 1) {
            $ref = new \ReflectionClass(get_class($this));
            if ($ref->getProperties()) {
                $propName = $ref->getProperties()[0]->getName();
                $this->$propName = $config['value'];
                $this->config[Inflector::tableize($propName)] = $config['value'];
            }
        } else {
            foreach ($config as $key => $value) {
                //dynamically added to avoid ide auto-complete for this property
                $this->config[Inflector::tableize($key)] = $value;
            }
        }
    }

    /**
     * Must return the array with plugin config
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config ?? [];
    }

    /**
     * Must return the plugin name to apply this config
     *
     * @return string
     */
    public function getName(): string
    {
        preg_match('/\w+$/', get_class($this), $matches);

        return strtolower($matches[0]);
    }
}
