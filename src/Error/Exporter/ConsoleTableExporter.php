<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Error\Exporter;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleTableExporter implements ErrorListExporterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'console';
    }

    /**
     * @inheritDoc
     */
    public function export($errors, OutputInterface $output): void
    {
        $rows = [];
        foreach ($errors as $error) {
            $rows[] = [$error->getCode(), $error->getMessage(), $error->getDescription()];
        }

        $table = new Table($output);
        $table->setHeaders(['Code', 'Text', 'Description'])
              ->setRows($rows);
        $table->render();
    }
}
