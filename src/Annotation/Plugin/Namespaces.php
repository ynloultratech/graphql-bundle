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

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'namespace';
    }
}
