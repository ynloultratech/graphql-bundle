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

use Symfony\Component\Console\Output\OutputInterface;
use Ynlo\GraphQLBundle\Error\MappedControlledError;

/**
 * Implement this interface and register the service as `graphql.error_list_exporter`
 * to add custom error list exporter
 */
interface ErrorListExporterInterface
{
    /**
     * Name of the format
     *
     * @return string
     */
    public function getName(): string;

    /**
     * @param array|MappedControlledError[] $errors
     * @param OutputInterface               $output
     *
     * @return mixed
     */
    public function export($errors, OutputInterface $output): void;
}
