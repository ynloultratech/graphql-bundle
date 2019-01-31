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

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class LexikJWTGraphiQLAuthenticator implements GraphiQLAuthenticationProviderInterface
{
    protected const SESSION_PATH = 'graphiql_jwt_api_token';

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoder;

    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var JWTTokenManagerInterface
     */
    protected $jwtTokenManager;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var AuthenticationSuccessHandler|null
     */
    protected $authenticationSuccessHandler;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * LexikJWTGraphiQLAuthenticator constructor.
     *
     * @param UserPasswordEncoderInterface $encoder
     * @param UserProviderInterface        $userProvider
     * @param JWTTokenManagerInterface     $jwtTokenManager
     * @param SessionInterface             $session
     */
    public function __construct(UserPasswordEncoderInterface $encoder, UserProviderInterface $userProvider, JWTTokenManagerInterface $jwtTokenManager, SessionInterface $session)
    {
        $this->encoder = $encoder;
        $this->userProvider = $userProvider;
        $this->jwtTokenManager = $jwtTokenManager;
        $this->session = $session;
        $this->config = [
            'username_label' => 'Username',
            'password_label' => 'Password',
        ];
    }

    /**
     * @param AuthenticationSuccessHandler|null $authenticationSuccessHandler
     */
    public function setAuthenticationSuccessHandler(?AuthenticationSuccessHandler $authenticationSuccessHandler): void
    {
        $this->authenticationSuccessHandler = $authenticationSuccessHandler;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @inheritDoc
     */
    public function requireUserData(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function buildUserForm(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'username',
                null,
                [
                    'label' => $this->config['username_label'],
                ]
            )
            ->add(
                'password',
                PasswordType::class,
                [
                    'label' => $this->config['password_label'],
                ]
            );
    }

    /**
     * @inheritDoc
     */
    public function login(?FormInterface $form = null)
    {
        if (!$form) {
            throw new \RuntimeException('This provider require a form');
        }

        $username = $form->get('username')->getData();
        $password = $form->get('password')->getData();

        try {
            $user = $this->userProvider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $exception) {
            $user = null;
        }

        if (!$user || !$this->encoder->isPasswordValid($user, $password)) {
            throw new AuthenticationFailedException();
        }

        $token = $this->jwtTokenManager->create($user);

        if ($this->authenticationSuccessHandler) {
            $this->authenticationSuccessHandler->handleAuthenticationSuccess($user, $token);
        }

        $this->session->set(self::SESSION_PATH, $token);
    }

    /**
     * @inheritDoc
     */
    public function logout()
    {
        $this->session->remove(self::SESSION_PATH);
    }

    /**
     * @inheritDoc
     */
    public function isAuthenticated(): bool
    {
        return $this->session->has(self::SESSION_PATH) && $this->session->get(self::SESSION_PATH);
    }

    /**
     * @inheritDoc
     */
    public function prepareRequest(GraphiQLRequest $request)
    {
        $token = null;
        if ($this->isAuthenticated()) {
            $token = $this->session->get(self::SESSION_PATH);
        }

        $request->addHeader('Authorization', sprintf('Bearer %s', $token));
    }
}
