# Getting Started

GraphQLBundle is a set of tools designed to easily create API projects based on [GraphQL](https://graphql.org/) & [Relay Specification](http://facebook.github.io/relay/docs/en/graphql-relay-specification.html)

## Prerequisites

This documentation assumes your familiarity with **GraphQL** concepts. 
If it is not the case - first learn about **GraphQL** on [the official website](http://graphql.org/learn/).

## Installation

Use composer to add the bundle as a requirement:

`composer require ynloultratech/graphql-bundle`

Add the bundle in the kernel

````php    
$bundles = [
    ...
    new Ynlo\GraphQLBundle\YnloGraphQLBundle(),
    new AppBundle\AppBundle(),
 ];
````

At this point its almost ready

## Configuration
     
#### Endpoint Route

GraphQL use one only endpoint to all queries & mutations to configure it, add the following route in your `routing.yml`

````yaml    
api:
  resource: '@YnloGraphQLBundle/Resources/config/routing/root.yml'
  prefix:   /api
````
     
At this point your GraphQL server under the `/api` route is ready, but does not have any data to serve.
  
#### Configure your first object
  
Add the following annotation to a doctrine entity

````php
use Doctrine\ORM\Mapping as ORM;
use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @GraphQL\ObjectType()
 */
class User
{
....
````

And the entity must implements `Ynlo\GraphQLBundle\Model\NodeInterface`.

> The node interface is a requirement in order to accomplish 
with the [Relay Specification](https://facebook.github.io/relay/) 
to create a [GlobalID](https://facebook.github.io/relay/docs/en/graphql-object-identification.html) for all nodes.
See [this documentation](object-identification.md) to know when is required the use of NodeInterface

At this point your entity should look like this:

````php

use Doctrine\ORM\Mapping as ORM;
use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @GraphQL\ObjectType()
 */
class User implements NodeInterface
{
````

Congrats, you have your first GraphQL object ready and you can start using the API now. 
Read more about creating [object types](definitions-object-type.md).

#### Using GraphiQL

[GraphiQL](https://github.com/graphql/graphiql) *(A graphical interactive in-browser GraphQL IDE)* ... 
in other words its a powerful tool to explore your api and interact with them.

GraphQLBundle has a GraphiQL integrated and ready to start using it, 
the only that you need is add the following route to your `routing.yml`

````yaml
api_explore:
      resource: '@YnloGraphQLBundle/Resources/config/routing/explorer.yml'
      prefix:   /api/explorer
````

> If you only need use the GraphiQL tool in a dev environment add the route configuration to `routing_dev.yml` instead.

Now you can use GraphiQL to interact with your API using the path `/api/explorer` in your browser.

At this point you can view two queries in the schema, `node(id)` and `nodes(ids)`

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

#### CRUD Operations

Your API is ready but is useless, you need more operations for all objects like, add, update etc..

This bundle has basic CRUD operations integrated for nodes, see [CRUD Operations documentation](crud-operations.md)
