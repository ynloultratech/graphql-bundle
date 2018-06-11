[GraphiQL](https://github.com/graphql/graphiql) *(A graphical interactive in-browser GraphQL IDE)* ... 
in other words it's a powerful tool to explore your API and interact with them.

GraphQLBundle has a GraphiQL integrated and ready to start using it, 
the only that you need is add the following route to your `routing.yml`

````yaml
api_explore:
      resource: '@YnloGraphQLBundle/Resources/config/routing/explorer.yml'
      prefix:   /explorer
      trailing_slash_on_root: false
````

> The trailing_slash_on_root option was introduced in Symfony 4.1.

Install required javascript and stylesheets:

    bin/console assets:install --symlink

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

> See [this documentation](../08_Reference/01_Object_ID.md) to view how encode and decode your database Ids.