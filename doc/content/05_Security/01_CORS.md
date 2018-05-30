GraphQLBundle has build-in support for 
[Cross-Origin Resource Sharing (CORS)](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing), 
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
use some advanced library like [NelmioCorsBundle](https://github.com/nelmio/NelmioCorsBundle) instead of build-in settings.