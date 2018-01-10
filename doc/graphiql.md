# GraphiQL

[GraphiQL](https://github.com/graphql/graphiql) *(A graphical interactive in-browser GraphQL IDE)* ... 
in other words its a powerful tool to explore your api and interact with them.

GraphQLBundle has a GraphiQL integrated and ready to start using it, 
the only that you need is add the following route to your `routing.yml`

````yaml
api_explore:
      resource: '@YnloGraphQLBundle/Resources/config/routing/explorer.yml'
      prefix:   /explorer
````

> If you only need use the GraphiQL tool in a dev environment add the route configuration to `routing_dev.yml` instead.

Now you can use GraphiQL to interact with your API using the path `/explorer` in your browser.

By default you can view two queries in the schema, `node(id)` and `nodes(ids)`

The following graphql example request for one user with database ID = 1

````graphql
query node{
  node(id: "VXNlcjox"){
   id
    ... on User{
      username
    }
  }
}
````

> See [this documentation](object-identification.md) to view how encode and decode your database Ids.

## Communicate with a Secure API
 
If you need expose GraphiQL in the production most likely your API 
require some authentication mechanism like OAuth2, JWT, API Key or any other.

Enable the authentication requirement in the bundle config.
````yaml
#config.yml

graphql:
    graphiql:
        authentication:
            required:  true
```` 
 
Configure one of the following authentication providers:

### JWT

Add the following config in your `config.yml`:

````yaml
#config.yml

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

### OAuth2

@TODO not implemented yet

### Custom Provider

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

> If your API is accessible without any credentials,
 but require credentials to do some advanced tasks, 
 you need configure `authentication.required:  false` 
 and configure one provider.
 
### Customizing the Explorer

The appearance of the explorer page can be configured:

````yaml
#config.yml

graphql:
    graphiql:
        title:                'GraphQL API Explorer'
        data_warning:         'Heads up! GraphQL Explorer makes use of your <strong>real</strong>, <strong>live</strong>, <strong>production</strong> data.'
        data_warning_dismissible: true
        data_warning_style:   danger # One of "info"; "warning"; "danger"
        template:             explorer.html.twig
        authentication:
            login_message:        'Start exploring GraphQL API queries using your account’s data now.'
````
