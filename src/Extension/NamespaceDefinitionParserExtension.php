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

use Doctrine\Common\Annotations\Reader;
use Ynlo\GraphQLBundle\Definition\DefinitionInterface;
use Ynlo\GraphQLBundle\Definition\DefinitionNamespace;
use Ynlo\GraphQLBundle\Definition\MetaAwareInterface;
use Ynlo\GraphQLBundle\Definition\NamespaceAwareDefinitionInterface;
use Ynlo\GraphQLBundle\Definition\Registry\Endpoint;

/**
 * This extension configure namespace in definitions
 * using definition node and bundle in the node
 */
class NamespaceDefinitionParserExtension extends AbstractGraphQLExtension
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $ignoreBundles = [];

    /**
     * @var bool
     */
    protected $groupByBundle = true;

    /**
     * @var bool
     */
    protected $groupByNode = true;

    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * PaginationExtension constructor.
     *
     * @param Reader $reader
     * @param array  $config
     */
    public function __construct(Reader $reader, $config = [])
    {
        $this->reader = $reader;
        $this->ignoreBundles = $config['bundles']['ignore'] ?? [];
        $this->aliases = $config['bundles']['aliases'] ?? [];
        $this->groupByBundle = $config['bundles']['enabled']  ?? true;
        $this->groupByNode = $config['nodes']['enabled']  ?? true;
    }

    /**
     * {@inheritdoc}
     */
    public function configureDefinition(DefinitionInterface $definition, \ReflectionClass $refClass, Endpoint $endpoint)
    {
        if (!$definition instanceof NamespaceAwareDefinitionInterface) {
            return;
        }

        $node = null;
        if ($this->groupByNode && $definition instanceof MetaAwareInterface && $definition->hasMeta('node')) {
            $node = $definition->getMeta('node');
        }

        $bundle = null;

        if ($this->groupByBundle) {
            if ($node) {
                if ($endpoint->hasType($node) && $nodeClass = $endpoint->getClassForType($node)) {
                    preg_match_all('/\\\\(\w+Bundle)\\\\/', $nodeClass, $matches);
                    if ($matches) {
                        $bundle = current(array_reverse($matches[1]));
                    }

                    if (isset($this->aliases[$bundle])) {
                        $bundle = $this->aliases[$bundle];
                    }

                    if ($bundle && in_array($bundle, $this->ignoreBundles)) {
                        $bundle = null;
                    }

                    if ($bundle) {
                        $bundle = preg_replace('/Bundle$/', null, $bundle);
                    }
                }
            }
        }

        if ($node || $bundle) {
            $definition->setNamespace(new DefinitionNamespace($bundle, $node));
        }
    }
}
