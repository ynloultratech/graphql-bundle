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
use Symfony\Component\Console\Output\StreamOutput;
use Ynlo\GraphQLBundle\Error\ControlledErrorManager;
use Ynlo\GraphQLBundle\Error\Exporter\ErrorListExporterInterface;

class ControlledErrorCommand extends Command
{
    /**
     * @var ControlledErrorManager
     */
    protected $errorManager;

    /**
     * @var array|iterable|ErrorListExporterInterface[]
     */
    protected $exporters = [];

    /**
     * ControlledErrorCommand constructor.
     *
     * @param ControlledErrorManager                      $errorManager
     * @param array|iterable|ErrorListExporterInterface[] $exporters
     */
    public function __construct(ControlledErrorManager $errorManager, iterable $exporters)
    {
        $this->errorManager = $errorManager;
        $this->exporters = $exporters;
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('graphql:error:list')
             ->setDescription('View, export and control your API errors.')
             ->addOption('exporter', 'x', InputOption::VALUE_REQUIRED, 'Exporter to user', 'console')
             ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Name of the file to save the error list, e.i. error_codes.md');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->errorManager->clear();//force reload
        $errors = $this->errorManager->all();

        $result = $output;
        if ($output = $input->getOption('output')) {
            $info = new \SplFileInfo($output);
            if (!file_exists($info->getPath())) {
                if (!mkdir($info->getPath(), 0777, true) && !is_dir($info->getPath())) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $info->getPath()));
                }
            }

            $result = new StreamOutput(fopen($output, 'w+b', false));
        }

        foreach ($this->exporters as $exporter) {
            if ($exporter->getName() === $input->getOption('exporter')) {
                $exporter->export($errors, $result);

                return;
            }
        }

        throw new \Exception('There are not any registered exporter to process the list of errors');
    }
}
