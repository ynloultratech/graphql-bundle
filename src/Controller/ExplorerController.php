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

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Ynlo\GraphQLBundle\GraphiQL\AuthenticationFailedException;
use Ynlo\GraphQLBundle\GraphiQL\GraphiQLAuthenticationProviderInterface;
use Ynlo\GraphQLBundle\GraphiQL\GraphiQLRequest;

class ExplorerController extends AbstractController
{
    private $config;
    private $provider;

    public function __construct(array $config, GraphiQLAuthenticationProviderInterface $provider = null)
    {
        $this->config = $config;
        $this->provider = $provider;
    }

    public function explorer(Request $request): Response
    {
        $form = null;
        $authenticationError = null;
        $isAuthenticated = false;

        if ($this->provider) {
            if ($this->provider->requireUserData()) {
                $builder = $this->createFormBuilder();
                $this->provider->buildUserForm($builder);
                $form = $builder->getForm();
            }

            if ($request->get('logout')) {
                $this->provider->logout();

                return $this->redirectToRoute('api_explore');
            }

            $form->handleRequest($request);
            $response = null;

            try {
                if ($form && $form->isSubmitted() && $form->isValid()) {
                    $this->provider->login($form);
                } elseif (!$form) {
                    $this->provider->login();
                }
            } catch (AuthenticationFailedException $exception) {
                if ($form) {
                    $form->addError(new FormError($exception->getMessage()));
                } else {
                    $authenticationError = $exception->getMessage();
                }
            }

            $isAuthenticated = $this->provider->isAuthenticated();
        }

        if ($this->config['documentation']['link'] ?? null) {
            try {
                $this->config['documentation']['link'] = $this->container
                    ->get('router')
                    ->generate($this->config['documentation']['link']);
            } catch (RouteNotFoundException $exception) {
                //do nothing, use the link as is
            }
        }

        return $this->render($this->config['template'], [
            'form' => $form ? $form->createView() : null,
            'favicon' => $this->config['favicon'] ?? null,
            'documentation' => $this->config['documentation'] ?? [],
            'isAuthenticated' => $isAuthenticated,
            'title' => $this->config['title'],
            'authenticationEnabled' => (bool) $this->provider,
            'authenticationRequired' => $this->config['authentication']['required'],
            'authenticationError' => $authenticationError,
            'hasAuthenticationError' => $authenticationError || ($form && $form->getErrors(true)->count()),
            'loginMessage' => $this->config['authentication']['login_message'],
            'dataWarningMessage' => $this->config['data_warning_message'],
            'dataWarningDismissible' => $this->config['data_warning_dismissible'],
            'dataWarningStyle' => $this->config['data_warning_style'],
        ]);
    }

    public function graphiQL()
    {
        $request = new GraphiQLRequest(
            $this->generateUrl('api_root'),
            [],
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        );
        if ($this->provider) {
            $this->provider->prepareRequest($request);
        }

        return $this->render('@YnloGraphQL/graphiql.html.twig', [
            'url' => $request->getUrl(),
            'method' => 'post',
            'headers' => $request->getHeaders(),
        ]);
    }
}
