<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\GraphiQL;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use function JmesPath\search;

/**
 * JWTGraphiQLAuthentication
 */
class JWTGraphiQLAuthentication implements GraphiQLAuthenticationProviderInterface
{
    private const SESSION_PATH = 'graphiql_jwt_api_token';

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param Router  $router
     * @param Session $session
     * @param array   $config
     */
    public function __construct(Router $router, Session $session, array $config)
    {
        $this->config = $config;
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function requireUserData(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function buildUserForm(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                $this->config['login']['username_parameter'] ?? 'username',
                null,
                [
                    'label' => $this->config['login']['username_label'] ?? 'Label',
                ]
            )
            ->add(
                $this->config['login']['password_parameter'] ?? 'password',
                PasswordType::class,
                [
                    'label' => $this->config['login']['password_label'] ?? 'Password',
                ]
            );
    }

    /**
     * @param null|FormInterface $form
     *
     * @throws \RuntimeException
     * @throws AuthenticationFailedException
     */
    public function login(?FormInterface $form = null)
    {
        if (!$form) {
            throw new \RuntimeException('This provider require a form');
        }

        $url = $this->config['login']['url'] ?? null;
        if ($this->router->getRouteCollection()->get($url)) {
            $url = $this->router->generate($url, [], Router::ABSOLUTE_URL);
        }

        $opts = [
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [],
        ];

        $paramsIn = $this->config['login']['parameters_in'] ?? 'form';
        if ('header' === $paramsIn) {
            $opts[CURLOPT_HEADER] = true;
            $headers = [];
            foreach ($form->getData() as $key => $value) {
                $headers[] = sprintf('%s: %s', $key, $value);
            }
            $opts[CURLOPT_HTTPHEADER] = array_merge(["Accept: application/json"], $headers);
        } elseif ('query' === $paramsIn) {
            $url .= '?'.http_build_query($form->getData(), null, '&');
        } else {
            $opts[CURLOPT_POSTFIELDS] = http_build_query($form->getData(), null, '&');
            $opts[CURLOPT_POST] = true;
        }

        $opts[CURLOPT_URL] = $url;

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);

        // Split the HTTP response into header and body.
        try {
            list($headers, $body) = explode("\r\n\r\n", $result);
            $headers = explode("\r\n", $headers);
        } catch (\ErrorException $exception) {
            throw new AuthenticationFailedException('Authentication Failed');
        }

        // We catch HTTP/1.1 4xx or HTTP/1.1 5xx error response.
        if (strpos($headers[0], 'HTTP/1.1 4') !== false || strpos($headers[0], 'HTTP/1.1 5') !== false) {
            $result = [
                'code' => 0,
                'message' => '',
            ];

            if (preg_match('/^HTTP\/1.1 ([0-9]{3,3}) (.*)$/', $headers[0], $matches)) {
                $result['code'] = $matches[1];
                $result['message'] = $matches[2];
            }

            // In case retrun with WWW-Authenticate replace the description.
            foreach ($headers as $header) {
                if (preg_match("/^WWW-Authenticate:.*error='(.*)'/", $header, $matches)) {
                    $result['message'] = $matches[1];
                }
            }

            throw new AuthenticationFailedException($result['message'] ?: 'Authentication Failed', $result['code'] ?: 401);
        }

        $tokenPath = $this->config['login']['response_token_path'] ?? 'token';
        $token = $body;

        if ($tokenPath) {
            $token = search($tokenPath, @json_decode($body));
        }

        if (!$token) {
            throw new AuthenticationFailedException('Authentication Failed');
        }

        $this->session->set(self::SESSION_PATH, $token);
    }

    /**
     * {@inheritDoc}
     */
    public function logout()
    {
        $this->session->remove(self::SESSION_PATH);
    }

    /**
     * {@inheritDoc}
     */
    public function isAuthenticated(): bool
    {
        return $this->session->has(self::SESSION_PATH) && $this->session->get(self::SESSION_PATH);
    }

    /**
     * {@inheritDoc}
     */
    public function prepareRequest(GraphiQLRequest $request)
    {
        $token = null;
        if ($this->isAuthenticated()) {
            $token = $this->session->get(self::SESSION_PATH);
            if ('header' === $this->config['requests']['token_in']) {
                $token = str_replace('{token}', $token, $this->config['requests']['token_template']);
            }
        }

        $tokenName = $this->config['requests']['token_name'];
        if ('header' === $this->config['requests']['token_in']) {
            $request->addHeader($tokenName, $token);
        } else {
            $request->addParameter($tokenName, $token);
        }
    }
}
