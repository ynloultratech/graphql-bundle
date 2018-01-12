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
    use NodeAwareDefinitionTrait;

    /**
     * @var string
     */
    protected $resolver;

    /**
     * @var string
     */
    protected $complexity;

    /**
     * @var array
     */
    protected $roles = [];

    /**
     * @return null|string
     */
    public function getResolver(): ?string
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

    public function getComplexity(): ?string
    {
        return $this->complexity;
    }

    public function setComplexity(?string $complexity): self
    {
        $this->complexity = $complexity;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
