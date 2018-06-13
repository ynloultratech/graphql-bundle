The schema of your project is published under the API 
endpoint with the name `/schema.graphql`. if your API endpoint is `https://api.example.com` the GraphQL schema is located at
`https://api.example.com/schema.graphql`.

# Schema for Consumers

The schema is used for consumers to update types, queries & mutations.

By default the schema is secured by the same firewall of the API endpoint. 
If your API require credentials, then must be used to access to the schema too. 
In a local environment when you need access to your own API *(developing a frontend UI)*
this can be annoying because you need update the schema every time.

In this scenario can disable the symfony firewall to schema in **dev** environment.

````yam;
security.yml

security:
    #...
    api:
            pattern:   ^/(?!explorer|login|schema.graphql)
            stateless: true
            provider: fos_userbundle
            guard:
                authenticators:
                    - lexik_jwt_authentication.jwt_token_authenticator

    access_control:
        - { path: ^/schema.graphql,  roles: IS_AUTHENTICATED_ANONYMOUSLY, allow_if: %kernel.debug% }
        - { path: ^/login,  roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/explorer,  roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/,       roles: IS_AUTHENTICATED_FULLY }
````

>>> Note the `allow_if: %kernel.debug%` in the schema access_control rule, 
is very important to avoid publish your schema without credentials in production too.