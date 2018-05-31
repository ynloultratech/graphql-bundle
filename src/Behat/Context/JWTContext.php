<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Ynlo\GraphQLBundle\Behat\Client\ClientAwareInterface;
use Ynlo\GraphQLBundle\Behat\Client\ClientAwareTrait;
use Ynlo\GraphQLBundle\Behat\GraphQLApiExtension;
use Ynlo\GraphQLBundle\Util\Json;

/**
 * JWT Context
 */
final class JWTContext implements Context, ClientAwareInterface
{
    use ClientAwareTrait;

    private static $tokens = [];

    protected $token;

    /**
     * @BeforeScenario
     */
    public function beforeScenario()
    {
        $this->token = null;
    }

    /**
     * @BeforeStep
     */
    public function beforeStep(BeforeStepScope $scope)
    {
        $config = GraphQLApiExtension::getConfig();
        if (!isset($config['jwt']['credentials'])) {
            return;
        }

        if ($this->token) {
            $this->setToken($this->token);

            return;
        }

        foreach ($config['jwt']['credentials'] as $name => $credentials) {
            if (\in_array($name, $scope->getFeature()->getTags())) {
                if (isset(self::$tokens[$name])) {
                    $this->token = self::$tokens[$name];
                    $this->setToken($this->token);
                    break;
                }

                $path = $config['jwt']['path'] ?? null;
                $username = $credentials['username'] ?? null;
                $password = $credentials['password'] ?? null;
                $usernameParam = $config['jwt']['username_parameter'] ?? null;
                $passwordParam = $config['jwt']['password_parameter'] ?? null;
                $parametersIn = $config['jwt']['parameters_in'] ?? 'form';
                $tokenPath = $config['jwt']['response_token_path'] ?? 'token';

                $headers = [];
                $parameters = [];

                switch ($parametersIn) {
                    case 'form':
                        $parameters[$usernameParam] = $username;
                        $parameters[$passwordParam] = $password;
                        $headers['HTTP_CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
                        break;
                    case 'query':
                        $parameters[$usernameParam] = $username;
                        $parameters[$passwordParam] = $password;
                        break;
                    case 'header':
                        $headers[$usernameParam] = $username;
                        $headers[$passwordParam] = $password;
                        break;
                }

                foreach ($headers as $key => $value) {
                    if (!preg_match('/^http_|HTTP_/', $value)) {
                        unset($headers[$key]);
                        $headers['HTTP_'.strtoupper($key)] = $value;
                    }
                }

                $this->client->request(
                    'post',
                    $path,
                    $parameters,
                    [],
                    $headers
                );

                $this->token = Json::getValue($this->client->getResponse(), $tokenPath);
                $this->client->restart();

                if (!$this->token) {
                    throw new \RuntimeException('Cant resolve a token using given credentials');
                }

                self::$tokens[$name] = $this->token;
                $this->setToken($this->token);
                break;
            }
        }
    }

    protected function setToken($token)
    {
        $tokenIn = $config['jwt']['token_in'] ?? 'header';
        $tokenName = $config['jwt']['token_name'] ?? 'Authorization';
        $tokenTemplate = $config['jwt']['token_template'] ?? 'Bearer {token}';

        if ($token) {
            $tokenValue = str_replace('{token}', $token, $tokenTemplate);
            switch ($tokenIn) {
                case 'header':
                    $this->client->setServerParameter(sprintf('HTTP_%s', $tokenName), $tokenValue);
                    break;
                case 'query':
                    $query = http_build_query([$tokenName => $tokenValue], null, '&');
                    $this->client->setEndpoint($this->client->getEndpoint().'?'.$query);
            }
        }
    }
}
