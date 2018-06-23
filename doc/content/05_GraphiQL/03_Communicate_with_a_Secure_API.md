If you need expose GraphiQL in the production most likely your API
require some authentication mechanism like OAuth2, JWT, API Key or any other.

Set authentication as required in the graphiql config:

````yaml
#config.yml

graphql:
    graphiql:
        authentication:
            required:  true
````

and configure one of the following authentication providers:

> If your API is accessible without any credentials, public API,
 but require credentials to do some advanced tasks,
 leave `authentication.required:  false`
 and configure one provider.

# LexikJWT

This provider use [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle) to provide authentication mechanisms to the API explorer.

Add the following config in your `config.yml`:

````yaml
graphql:
    graphiql:
        authentication:
            provider:
                lexik_jwt:
                    user_provider: fos_userbundle
````

> The `user_provider` must match with the user provider you want to use.

# JWT

>> This provider has been deprecated since `v1.1`.

Add the following config in your `config.yml`:

````yaml
graphql:
    graphiql:
        authentication:
            provider:
                jwt:
                  login:
                      url: api_login
````
> The login url should be the url to retrieve the token, can be a route name or URI.

Full configuration for JWT provider:

````yaml
jwt:
    enabled:              false
    login:

        # Route name or URI to make the login process to retrieve the token.
        url:                  ~ # Required
        username_parameter:   username
        password_parameter:   password

        # How pass parameters to request the token
        parameters_in:        form # One of "form"; "query"; "header"

        # Where the token should be located in the response in case of JSON, set null if the response is the token.
        response_token_path:  token
    requests:
        # Where should be located the token on every request
        token_in:             header # One of "query"; "header"

        # Name of the token in query or header name
        token_name:           Authorization

        # Customize how the token should be send,  use the place holder {token} to replace for current token
        token_template:       'Bearer {token}'
````

> By default the JWT configuration is ready to work with [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle) out of the box.

# OAuth2

@TODO not implemented yet

# Custom Provider

The custom provider is used to pass the name of the
service to use as authentication provider.
The service must implements `\Ynlo\GraphQLBundle\GraphiQL\GraphiQLAuthenticationProviderInterface`
````yaml
#config.yml

graphql:
    graphiql:
        authentication:
            provider:
                custom: my_custom_graphiql_auth
````
