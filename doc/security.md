# Security

The security is a very important thing,
 read carefully the following sections to secure your API.

## Cross-Origin Resource Sharing

GraphQLBundle has build-in support for 
[CORS](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing), 
by default this option is disabled.

Can be enabled in the bundle config:

````yaml
#config.yml

graphql:
    cors: true
````
The default configuration is enough for many scenarios using a GraphQL API, 
but if you need can change some basic settings.

````yaml
cors:
    enabled:              true
    allow_credentials:    true
    allow_headers:

        # Defaults:
        - Origin
        - Content-Type
        - Accept
        - Authorization
    max_age:              3600
    allow_methods:

        # Defaults:
        - POST
        - GET
        - OPTIONS
    allow_origins:

        # Default:
        - *
````

If you need more control over CORS settings, 
use [NelmioCorsBundle](https://github.com/nelmio/NelmioCorsBundle) instead of build-in settings.

## Authentication

In order to keep simple and customizable this bundle, 
 doest not have integrated any authentication system. 

The following bundles can be easily integrated and are powerful:
 
- OAuth2 - [FOSOAuthServerBundle](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle)
- JWToken - [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle)

## Roles & Permissions

@TODO not implemented yet

## Query Complexity Analysis

@TODO not implemented yet

## Limiting Query Depth

@TODO not implemented yet

## Disabling Introspection

@TODO not implemented yet