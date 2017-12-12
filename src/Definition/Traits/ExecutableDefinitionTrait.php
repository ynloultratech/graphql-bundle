<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Definition\Traits;

/**
 * Trait ExecutableDefinitionTrait
 */
trait ExecutableDefinitionTrait
{
    use DefinitionTrait;
    use ArgumentAwareTrait;
    use DeprecateTrait;
    use TypeAwareDefinitionTrait;
    use MetaAwareTrait;
    use NamespaceAwareTrait;

    protected $resolver;

    /**
     * @return null|string
     */
    public function getResolver():?string
    {
        return $this->resolver;
    }

    /**
     * @param null|string $resolver
     *
     * @return $this
     */
    public function setResolver(?string $resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }
}
