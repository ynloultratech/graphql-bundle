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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExplorerController
 */
class ExplorerController extends Controller
{
    /**
     * @return Response
     */
    public function explorerAction()
    {
        $params = [
            'url' => $this->generateUrl('api_root'),
            'method' => 'post',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        return $this->render('@YnloGraphQL/explorer.twig', $params);
    }
}
