<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Context\Loader;

use Behat\Behat\Context\Environment\UninitializedContextEnvironment;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\SuiteTester;

/**
 * Auto-load all API contexts
 *
 * @TODO: add some flat to only register for suites tagged with @api
 */
class ContextLoader implements SuiteTester
{
    /**
     * @var SuiteTester
     */
    protected $baseTester;

    /**
     * @var string[]
     */
    protected $contexts = [];

    /**
     * ContextAutoLoader constructor.
     *
     * @param SuiteTester $baseTester
     */
    public function __construct(SuiteTester $baseTester, $contexts = [])
    {
        $this->baseTester = $baseTester;
        $this->contexts = $contexts;
    }

    public function setUp(Environment $env, SpecificationIterator $iterator, $skip)
    {
        /** @var UninitializedContextEnvironment $env */
        foreach ($this->contexts as $context) {
            $env->registerContextClass($context);
        }

        return $this->baseTester->setUp($env, $iterator, $skip);
    }

    public function test(Environment $env, SpecificationIterator $iterator, $skip)
    {
        return $this->baseTester->test($env, $iterator, $skip);
    }

    public function tearDown(Environment $env, SpecificationIterator $iterator, $skip, TestResult $result)
    {
        return $this->baseTester->tearDown($env, $iterator, $skip, $result);
    }
}
