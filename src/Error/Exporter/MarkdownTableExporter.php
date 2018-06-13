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
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\OutputInterface;

class MarkdownTableExporter implements ErrorListExporterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'markdown';
    }

    /**
     * @inheritDoc
     */
    public function export($errors, OutputInterface $output): void
    {
        $rows = [
            ['Code', 'Text', 'Description'],
            ['---', '---', '---'],
        ];
        foreach ($errors as $error) {
            $description = str_replace(["\n\n", "\n"], ['<br>', null], $error->getDescription());
            $rows[] = [
                sprintf('**%s**', $error->getCode()),
                sprintf('**%s**', $error->getMessage()),
                $description,
            ];
        }

        $table = new Table($output);

        $styleGuide = new TableStyle();
        $styleGuide->setHorizontalBorderChar('')
                   ->setVerticalBorderChar('|')
                   ->setCrossingChar(' ')
                   ->setCellHeaderFormat('%s');

        $table->setStyle($styleGuide);
        $table->setRows($rows);
        $table->render();
    }
}