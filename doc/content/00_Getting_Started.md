GraphQLBundle is a set of tools designed to easily create API projects based on [GraphQL](https://graphql.org/) & [Relay Specification](http://facebook.github.io/relay/docs/en/graphql-relay-specification.html)

# Prerequisites

This documentation assumes your familiarity with **GraphQL** concepts. 
If it is not the case - first learn about **GraphQL** on [the official website](http://graphql.org/learn/).

# Installation

Use composer to add the bundle as a requirement:

`composer require ynloultratech/graphql-bundle`

Add the bundle in the kernel

````php    
$bundles = [
    ...
    new Ynlo\GraphQLBundle\YnloGraphQLBundle(),
    new App\AppBundle(),
 ];
````

> In Symfony4 the bundle is automatically registered after composer installation.

At this point it's almost ready

# Configuration
     
## Endpoint Route

GraphQL use one only endpoint for all queries & mutations. 
To configure it, add the following route in your `routing.yml` or `routes.yaml` in symfony4.

````yaml    
api:
  resource: '@YnloGraphQLBundle/Resources/config/routing/root.yml'
  prefix:   /api
  trailing_slash_on_root: false
````

> The `trailing_slash_on_root` option was introduced in Symfony 4.1.
     
Now your GraphQL server under the `/api` route is ready, but does not have any data to serve.
  
# Configure your first object
  
GraphQL is based on objects, fields and types, is required al least 
register one object to has something to serve.

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

The entity must implements `Ynlo\GraphQLBundle\Model\NodeInterface`.

> The node interface is a requirement in order to accomplish 
with the [Relay Specification](https://facebook.github.io/relay/) 
to create a [GlobalID](https://facebook.github.io/relay/docs/en/graphql-object-identification.html) for all nodes.
See [this documentation](08_Reference/01_Object_ID.md) to know when is required the use of NodeInterface

Your entity should look like this:

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
Read more about creating [object types](03_Type_Definitions/00_Type_System.md).

## Using GraphiQL

Can configure [GraphiQL](05_GraphiQL/01_Installation.md) to interact with your API in dev or production environments.

## CRUD Operations

Your API is ready but is useless, you need more operations for all objects like, add, update etc..

This bundle has basic CRUD operations integrated for nodes, see [CRUD Operations documentation](02_Crud_Operations/00_Overview.md)
