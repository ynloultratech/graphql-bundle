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
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ynlo\GraphQLBundle\GraphiQL\AuthenticationFailedException;
use Ynlo\GraphQLBundle\GraphiQL\GraphiQLAuthenticationProviderInterface;
use Ynlo\GraphQLBundle\GraphiQL\GraphiQLRequest;

/**
 * Class ExplorerController
 */
class ExplorerController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function explorerAction(Request $request)
    {
        $form = null;
        $authenticationError = null;
        $isAuthenticated = false;
        $config = $this->getParameter('graphql.graphiql');

        if ($provider = $this->getAuthenticationProvider()) {
            if ($provider->requireUserData()) {
                $builder = $this->createFormBuilder();
                $provider->buildUserForm($builder);
                $form = $builder->getForm();
            }

            if ($request->get('logout')) {
                $provider->logout();

                return $this->redirectToRoute('api_explore');
            }

            $form->handleRequest($request);
            $response = null;

            try {
                if ($form && $form->isSubmitted() && $form->isValid()) {
                    $provider->login($form);
                } elseif (!$form) {
                    $provider->login();
                }
            } catch (AuthenticationFailedException $exception) {
                if ($form) {
                    $form->addError(new FormError($exception->getMessage()));
                } else {
                    $authenticationError = $exception->getMessage();
                }
            }

            $isAuthenticated = $provider->isAuthenticated();
        } else {
            if ($config['authentication']['required']) {
                throw new \RuntimeException('Configure a valid provider to use GraphiQL with authentication');
            }
        }

        return $this->render(
            $config['template'],
            [
                'form' => $form ? $form->createView() : null,
                'isAuthenticated' => $isAuthenticated,
                'title' => $config['title'],
                'authenticationEnabled' => (bool) $provider,
                'authenticationRequired' => $config['authentication']['required'],
                'authenticationError' => $authenticationError,
                'hasAuthenticationError' => $authenticationError || ($form && $form->getErrors(true)->count()),
                'loginMessage' => $config['authentication']['login_message'],
                'dataWarningMessage' => $config['data_warning_message'],
                'dataWarningDismissible' => $config['data_warning_dismissible'],
                'dataWarningStyle' => $config['data_warning_style'],
            ]
        );
    }

    /**
     * @return Response
     */
    public function graphiQLAction()
    {
        $request = new GraphiQLRequest(
            $this->generateUrl('api_root'),
            [],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        );
        if ($provider = $this->getAuthenticationProvider()) {
            $provider->prepareRequest($request);
        }

        $params = [
            'url' => $request->getUrl(),
            'method' => 'post',
            'headers' => $request->getHeaders(),
        ];

        return $this->render('@YnloGraphQL/graphiql.twig', $params);
    }

    /**
     * @return GraphiQLAuthenticationProviderInterface|object|null
     */
    protected function getAuthenticationProvider()
    {
        $providerName = $this->getParameter('graphql.graphiql_auth_provider');

        if ($providerName) {
            return $this->get($providerName);
        }

        return null;
    }
}
