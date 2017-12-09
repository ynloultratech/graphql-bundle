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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SchemaController
 */
class SchemaController extends Controller
{
    /**
     * @return Response
     */
    public function schemaAction(): Response
    {
        $schema = $this->get('Ynlo\GraphQLBundle\Schema\SchemaCompiler')->compile();

        return new Response(SchemaPrinter::doPrint($schema));
    }
}
