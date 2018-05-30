During compilation time all mapped Object types, Mutations, Queries, Fields, etc. 
are compiled into GraphQL definitions. These definitions are saved in cache 
for fast reloading in production environments. A plugin is a way to override or
customize how definitions are compiled during compilation time,
a plugin can be used to add extra features to your schema.

# How use plugins?

Some plugins are always active and no require any user action or special config. 
Others are active or customized using advanced configuration.

The way to configure plugins is using the `options` attribute existent in many
annotations.

````
 * @GraphQL\ObjectType()
 * @GraphQL\QueryList(options={
 *      "pagination": { "limit": 10 }
 * })
````

> The above configuration limit to 10 the max number of records 
to fetch when use pagination for this node. 

to know available options for a specific plugin can use the following command:

    /bin/console graphql:plugins pagination

or this other to get the list of all available plugins and configuration.

    /bin/console graphql:plugins

# Build-in Plugins

The system comes with some build-in plugin out of the box. 
These plugins add some extra and very useful features.

## Pagination
 @TODO
## Mutation Form
 @TODO
## User Roles
 @TODO
## Namespace
  @TODO
## Reorder
  @TODO
## CRUD Extension Resolver
  @TODO
