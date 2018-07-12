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

/**
 * @Annotation
 */
class Namespaces extends PluginConfigAnnotation
{
    /**
     * Set custom namespace,
     * for example:
     *
     * billing/invoices
     *
     * @var string
     */
    public $namespace;

    /**
     * Name to use as alias for this operation.
     *
     * By default the same operation name is used,
     * but removing any suffix containing the node name,
     * example `AddPost` when is namespaced is converted to `add`
     * inside `posts` namespace. If you wat to use for example `create` instead
     * can set this as alias.
     *
     * @var bool
     */
    public $alias;

    /**
     * @var bool
     */
    public $enabled;

    /**
     * @var string
     */
    public $node;

    /**
     * @var string
     */
    public $bundle;

    public function __construct(array $config = [])
    {
        if (isset($config['value']) && \count($config) === 1 && \is_bool($config['value'])) {
            $config['enabled'] = $config['value'];
            unset($config['value']);
        }
        parent::__construct($config);
    }


    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'namespace';
    }
}
