<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Transformer;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Transformation\Transformer\ArgumentTransformer;
use Symfony\Component\HttpKernel\KernelInterface;
use Ynlo\GraphQLBundle\Behat\Fixtures\FixtureManager;

/**
 * Transform named fixtures to fixtures global ID,
 * the fixture name should be prefixed with #
 *
 * @example "#user1" => "VXNlcjox"
 */
class TransformFixtureNameToGlobalId implements ArgumentTransformer
{
    private const PATTERN = '/^#/';

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var FixtureManager
     */
    protected $fixtureManager;

    public function __construct(KernelInterface $kernel, FixtureManager $fixtureManager)
    {
        $this->kernel = $kernel;
        $this->fixtureManager = $fixtureManager;
    }

    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        return preg_match(self::PATTERN, $argumentValue);
    }

    public function transformArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        $name = preg_replace(self::PATTERN, null, $argumentValue);

        return $this->fixtureManager->getFixtureGlobalId($name);
    }
}
