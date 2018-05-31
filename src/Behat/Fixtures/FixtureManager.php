<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Fixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpKernel\KernelInterface;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Model\ID;
use Ynlo\GraphQLBundle\Model\NodeInterface;

class FixtureManager
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var ReferenceRepository
     */
    protected $repository;

    /**
     * FixtureManager constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return ReferenceRepository
     */
    public function getRepository(): ReferenceRepository
    {
        return $this->repository;
    }

    /**
     * @param ReferenceRepository $repository
     *
     * @return FixtureManager
     */
    public function setRepository(ReferenceRepository $repository): FixtureManager
    {
        $this->repository = $repository;

        return $this;
    }

    public function getFixture(string $name)
    {
        return $this->getRepository()->getReference($name);
    }

    public function getFixtureGlobalId(string $name)
    {
        $fixture = $this->getFixture($name);
        if ($fixture instanceof NodeInterface) {
            $nodeType = $this->kernel
                ->getContainer()
                ->get(DefinitionRegistry::class)
                ->getEndpoint()
                ->getTypeForClass(ClassUtils::getClass($fixture));

            $id = $fixture->getId();

            return ID::encode($nodeType, $id);
        }

        throw new \RuntimeException(
            sprintf(
                'Can\'t get global ID for given fixture name "%s", because "%s" does not implements NodeInterface.',
                $name,
                ClassUtils::getClass($fixture)
            )
        );
    }
}
