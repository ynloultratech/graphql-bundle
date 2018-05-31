The complete bundle configuration:

````yaml
graphql:
    endpoints:

        # Prototype
        name:
            roles:                []
            host:                 ~ # Example: ^api\.backend\.
            path:                 ~ # Example: /backend

    # Use alias to refer to multiple endpoints using only one name
    endpoint_alias:

        # Prototype
        name:                 ~

    # Endpoint to apply to all definitions without explicit endpoint.
    endpoint_default:     ~
    cors:
        enabled:              false
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
    graphiql:
        title:                'GraphQL API Explorer'
        data_warning_message: 'Heads up! GraphQL Explorer makes use of your <strong>real</strong>, <strong>live</strong>, <strong>production</strong> data.'
        data_warning_dismissible: true
        data_warning_style:   danger # One of "info"; "warning"; "danger"
        template:             '@YnloGraphQL/explorer.html.twig'
        authentication:

            # The API require credentials to make any requests, 
            # if this value is FALSE and a provider is specified the authentication is optional.
            required:             false
            login_message:        'Start exploring GraphQL API queries using your account’s data now.'
            provider:
                jwt:
                    enabled:              false
                    login:

                        # Route name or URI to make the login process to retrieve the token.
                        url:                  ~ # Required
                        username_parameter:   username
                        username_label:       Username
                        password_parameter:   password
                        password_label:       Password

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

                # Configure custom service to use as authentication provider
                custom:               null
    definitions:
        plugins:
            pagination:

                # Maximum limit allowed for all paginations
                limit:                100

            # Group GraphQL schema using namespaced schemas. 
            # On large schemas is  helpful to keep schemas grouped by bundle and node
            namespaces:
                enabled:              false

                # Group each bundle into a separate schema definition
                bundles:
                    enabled:              true

                    # The following suffix will be used for bundle query groups
                    query_suffix:         BundleQuery

                    # The following suffix will be used for bundle mutation groups
                    mutation_suffix:      BundleMutation

                    # The following bundles will be ignore for grouping, all definitions will be placed in the root query or mutation
                    ignore:

                        # Default:
                        - AppBundle

                    # Define aliases for bundles to set definitions inside other desired bundle name. 
                    # Can be used to group multiple bundles or publish a bundle with a different name
                    aliases:              # Example: SecurityBundle: AppBundle

                        # Prototype
                        name:                 ~

                # Group queries and mutations of the same node into a node specific schema definition.
                nodes:
                    enabled:              true

                    # The following suffix will be used to create the name for queries to the same node
                    query_suffix:         Query

                    # The following suffix will be used to create the name for mutations to the same node
                    mutation_suffix:      Mutation

                    # The following nodes will be ignore for grouping, all definitions will be placed in the root query or mutation
                    ignore:

                        # Default:
                        - Node

                    # Define aliases for nodes to set definitions inside other desired node name. 
                    # Can be used to group multiple nodes or publish a node with a different group name
                    aliases:              # Example: InvoiceItem: Invoice

                        # Prototype
                        name:                 ~
    security:
        enabled:              false
        validation_rules:

            # Query complexity score before execution. (Recommended >= 200)
            query_complexity:     0

            # Max depth of the query. (Recommended >= 11)
            query_depth:          0
            disable_introspection: false
````