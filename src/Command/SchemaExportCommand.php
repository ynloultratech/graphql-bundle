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
use Ynlo\GraphQLBundle\Schema\SchemaExporter;

class SchemaExportCommand extends Command
{
    /**
     * @var SchemaExporter
     */
    protected $exporter;

    /**
     * GraphQLSchemaExportCommand constructor.
     *
     * @param SchemaExporter $exporter
     */
    public function __construct(SchemaExporter $exporter)
    {
        $this->exporter = $exporter;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('graphql:schema:export')
             ->setDescription('Export your schema.')
             ->addOption('endpoint', null, InputOption::VALUE_REQUIRED, 'Name of the endpoint to export', DefinitionRegistry::DEFAULT_ENDPOINT)
             ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Name of the file to save the schema, e.i. schema.graphql or schema.json')
             ->addOption('json', null, InputOption::VALUE_NONE, 'Create json output, automatically used if the output contains json extension');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $endpoint = $input->getOption('endpoint');
        $asJson = $input->getOption('json');
        $outputName = $input->getOption('output');
        if (preg_match('/\.json$/', $outputName)) {
            $asJson = true;
        }

        $schema = $this->exporter->export($endpoint, $asJson);
        if ($outputName) {
            file_put_contents($outputName, $schema);
        } else {
            $output->write($schema);
        }
    }
}
