<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Controller;

use GraphQL\Utils\SchemaPrinter;
use Symfony\Component\HttpFoundation\Response;
use Ynlo\GraphQLBundle\Schema\SchemaCompiler;

class SchemaController
{
    private $compiler;

    public function __construct(SchemaCompiler $compiler)
    {
        $this->compiler = $compiler;
    }

    public function __invoke(): Response
    {
        $schema = $this->compiler->compile();

        return new Response(SchemaPrinter::doPrint($schema));
    }
}
