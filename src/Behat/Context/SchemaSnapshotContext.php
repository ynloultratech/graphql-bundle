<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Ynlo\GraphQLBundle\Schema\SchemaSnapshot;

class SchemaSnapshotContext implements Context, KernelAwareContext
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var string
     */
    private static $featureDir;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var array
     */
    private $savedSnapshot = [];

    /**
     * @var array
     */
    private $currentSnapshot = [];

    /**
     * @inheritDoc
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @BeforeFeature
     */
    public static function beforeFEature(BeforeFeatureScope $scope)
    {
        self::$featureDir = (new \SplFileInfo($scope->getFeature()->getFile()))->getPathInfo()->getRealPath();
    }

    /**
     * @Given /^previous schema snapshot of "([^"]*)" endpoint$/
     */
    public function previousSnapshotOfEndpointSchema($endpoint)
    {
        $this->endpoint = $endpoint;
        $file = sprintf('%s/%s.snapshot.json', self::$featureDir, $endpoint);
        $this->savedSnapshot = json_decode(file_get_contents($file), true);
    }

    /**
     * @When /^compare with current schema$/
     */
    public function compareWithCurrentSchema()
    {
        $this->currentSnapshot = $this->kernel
            ->getContainer()
            ->get(SchemaSnapshot::class)
            ->createSnapshot($this->endpoint);
    }

    /**
     * @Then /^current schema is compatible with latest snapshot$/
     */
    public function currentSchemaIsCompatibleWithTheLatestSnapshot()
    {
        try {
            Assert::assertArraySubset($this->savedSnapshot, $this->currentSnapshot);
        } catch (ExpectationFailedException $exception) {
            $changeSet = $this->arrayRecursiveDiff($this->savedSnapshot, $this->currentSnapshot);
            $changeSetString = json_encode($changeSet, JSON_PRETTY_PRINT);
            $comparison = new ComparisonFailure($changeSet, [], $changeSetString, '');

            throw new ExpectationFailedException(
                'Your current schema is not compatible with the latest snapshot, some fields, types or arguments has been deleted.',
                $comparison
            );
        }
    }

    /**
     * @Given /^current schema is same after latest snapshot$/
     */
    public function currentSchemaDoesNotExposeMoreDefinitionsThanLatestSnapshot()
    {
        try {
            Assert::assertEquals($this->currentSnapshot, $this->savedSnapshot);
        } catch (ExpectationFailedException $exception) {
            $current = json_encode($this->savedSnapshot, JSON_PRETTY_PRINT);
            $snapshot = json_encode($this->currentSnapshot, JSON_PRETTY_PRINT);
            $comparison = new ComparisonFailure($this->savedSnapshot, $this->currentSnapshot, $current, $snapshot);

            throw new ExpectationFailedException('Your current schema has ben changed after latest snapshot.', $comparison);
        }
    }

    private function arrayRecursiveDiff($aArray1, $aArray2)
    {
        $aReturn = [];

        foreach ($aArray1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
                } else {
                    if ($mValue != $aArray2[$mKey]) {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            } else {
                $aReturn[$mKey] = $mValue;
            }
        }

        return $aReturn;
    }
}
