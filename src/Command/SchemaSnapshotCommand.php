<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ynlo\GraphQLBundle\Definition\Registry\DefinitionRegistry;
use Ynlo\GraphQLBundle\Schema\SchemaSnapshot;

class SchemaSnapshotCommand extends Command
{

    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var SchemaSnapshot
     */
    protected $schemaSnapshot;

    /**
     * @var array
     */
    protected $endpoints;

    /**
     * GraphQLSchemaExportCommand constructor.
     *
     * @param SchemaSnapshot $schemaSnapshot
     * @param array          $endpoints
     * @param string         $projectDir
     */
    public function __construct(SchemaSnapshot $schemaSnapshot, $endpoints, $projectDir)
    {
        $this->schemaSnapshot = $schemaSnapshot;
        $this->endpoints = $endpoints;
        $this->projectDir = $projectDir;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('graphql:schema:snapshot')
             ->setDescription('Create a snapshot of your schema to compare using behat tests.')
             ->addOption('endpoint', null, InputOption::VALUE_REQUIRED, 'Name of the endpoint to export', DefinitionRegistry::DEFAULT_ENDPOINT)
             ->addOption('all', 'a', InputOption::VALUE_NONE, 'Create snapshot for all registered endpoints')
             ->addOption('strict', null, InputOption::VALUE_NONE, 'When use strict mode the snapshot must be updated every time your schema change')
             ->addOption('features', null, InputOption::VALUE_REQUIRED, 'Path where should be located the generated features and fixtures');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getOption('features');
        if (!$dir) {
            $dir = $this->projectDir.'/features';
        }

        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }

        $strictMode = $input->getOption('strict');

        $all = $input->getOption('all');
        if ($all && $this->endpoints) {
            foreach ($this->endpoints as $endpoint) {
                $this->createSnapshot($endpoint, $dir, $strictMode);
            }
        } else {
            $this->createSnapshot($input->getOption('endpoint'), $dir, $strictMode);
        }

        return 0;
    }

    /**
     * @param string      $endpoint
     * @param string|null $where
     * @param bool        $strictMode
     *
     * @throws \Exception
     */
    private function createSnapshot(string $endpoint, ?string $where = null, $strictMode = false): void
    {
        if ($this->endpoints && !\in_array($endpoint, $this->endpoints)) {
            if (DefinitionRegistry::DEFAULT_ENDPOINT === $endpoint) {
                throw new \Exception('Must specify a valid endpoint name or use the `--all` option');
            }

            throw new \Exception(
                sprintf('The are no valid endpoint called `%s`.', $endpoint)
            );
        }

        $snapshot = $this->schemaSnapshot->createSnapshot($endpoint);
        file_put_contents(sprintf('%s/%s.snapshot.json', $where, $endpoint), json_encode($snapshot, JSON_PRETTY_PRINT));
        $this->updateFeatureFile($where, $endpoint, $strictMode);
    }

    private function updateFeatureFile($where, $endpoint, $strictMode = false)
    {
        $header = <<<EOS
Feature: Schema Snapshot

EOS;
        $strictStep = null;
        if ($strictMode) {
            $strictStep = "    And current schema is same after latest snapshot\n";
        }
        $scenario = <<<EOS

  Scenario: Verify "$endpoint" Endpoint
    Given previous schema snapshot of "$endpoint" endpoint
    When compare with current schema
    Then current schema is compatible with latest snapshot
$strictStep
EOS;


        $featureFile = sprintf('%s/snapshot.feature', $where);
        if (!file_exists($featureFile)) {
            file_put_contents($featureFile, $header);
        }

        if (!preg_match("/\"$endpoint\" endpoint/", (string) file_get_contents($featureFile))) {
            file_put_contents($featureFile, $scenario, FILE_APPEND);
        }
    }
}
