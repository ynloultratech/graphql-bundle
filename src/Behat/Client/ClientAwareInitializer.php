<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Client;

use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\EventDispatcher\Tester\EventDispatchingScenarioTester;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Behat\Tester\ScenarioTester;
use Behat\Behat\Tester\StepTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Inject GraphQLClient instance on very context implementing ClientAwareInterface
 */
class ClientAwareInitializer implements ScenarioTester
{
    /**
     * @var StepTester
     */
    private $baseTester;

    /**
     * @var GraphQLClient
     */
    private $client;

    public function __construct(EventDispatchingScenarioTester $baseTester, GraphQLClient $client)
    {
        $this->baseTester = $baseTester;
        $this->client = $client;
    }

    public function setUp(Environment $env, FeatureNode $feature, ScenarioInterface $step, $skip)
    {
        return $this->baseTester->setUp($env, $feature, $step, $skip);
    }

    public function test(Environment $env, FeatureNode $feature, ScenarioInterface $step, $skip)
    {
        /** @var InitializedContextEnvironment $env */
        foreach ($env->getContexts() as $context) {
            if ($context instanceof ClientAwareInterface) {
                $context->setClient($this->client);
            }
        }

        return $this->baseTester->test($env, $feature, $step, $skip);
    }

    public function tearDown(Environment $env, FeatureNode $feature, ScenarioInterface $step, $skip, TestResult $result)
    {
        return $this->baseTester->tearDown($env, $feature, $step, $skip, $result);
    }
}