---
title: JSON Web Tokens (JWT)
---

The best and easy way to integrate [JWT](https://jwt.io) authentication to your API is using 
[LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle).

> This bundle require a previously configured user provider or install FOSUserBundle.

Install and configure LexikJWTAuthenticationBundle as described in 
the [documentation](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md#installation).

The following configuration is a full example of the `securitty.yml` of any symfony application working like a API.

````yaml
security:
    encoders:
        App\Entity\User:
            algorithm: md5
            encode_as_base64: false
            iterations: 1

    role_hierarchy:
        ROLE_API:       ROLE_USER

    providers:
        fos_userbundle:
            id: fos_user.user_provider.username

    firewalls:
      options:
            methods: [OPTIONS]
            pattern: ^/
            security: false
      dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
      login:
          pattern:  ^/login
          stateless: true
          anonymous: true
          provider: fos_userbundle
          form_login:
              check_path:               /login
              success_handler:          lexik_jwt_authentication.handler.authentication_success
              failure_handler:          lexik_jwt_authentication.handler.authentication_failure
              require_previous_session: false
              post_only:                true
              #paramaters names changes for readability in API usage
              username_parameter:       username
              password_parameter:       password
      api:
          pattern:   ^/(?!explorer|login)
          stateless: true
          provider: fos_userbundle
          guard:
              authenticators:
                  - lexik_jwt_authentication.jwt_token_authenticator

    access_control:
        - { path: ^/login,  roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/explorer,  roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/,       roles: IS_AUTHENTICATED_FULLY }
````

In the above example:

- The application root is the API, then the endpoint is the domain. Can change to use a path like `/api`.
- The `username_parameter` and `password_parameter` has been changed to remove the `_` prefix
- `login` and `explorer` allow ANONYMOUSLY access, but both methods require valid credentials. See [how configure GraphiQL with authentication](../../05_GraphiQL/01_Installation.md) 

> This is a basic example and you can configure you application like you want.