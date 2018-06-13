The “endpoint” of a API is simply a unique URL that represents an object or 
collection of objects. GraphQL commonly work with one single endpoint and that is OK, 
but sometimes you need hide some part of the schema for non authorized users.
This is exactly `endpoints` do for you in GraphQLBundle.

# Features

- Create endpoints for different audiences
- GraphQL schema based on user roles, url path or host

# How it works?

If you are implementing a API with multiple possible audiences or consumers, 
it's very possible you need hide or show some part of the schema based on some rules.

For example is not a good idea expose operations like `allUsers`, `addUser`, `removeUser` 
in the frontend of your app. 
Commonly this type of actions are only necessary in the backend. 
You can check if the user has proper permissions before execute the operation, 
but this is not the best solution, because the schema still displaying these operations.
If your security check fails or is missing, you are exposing functional and dangerous operations.

In the other hand, `endpoints` hide this sensitive operations for users without proper access,
and the GraphQL schema works like a firewall because can't execute a non existent operation. 

# Configuration

To activate endpoints go to your `config.yml` and add the following config:

````yaml
graphql:
    endpoints:
        admin:
            roles: [IS_AUTHENTICATED_FULLY, ROLE_ADMIN]
        frontend:
            roles: IS_AUTHENTICATED_ANONYMOUSLY
````

The above configuration expose two endpoints `admin` and `frontend`. 
This configuration use the same path for all endpoints, the schema change based
on current user credentials.

Alternatively you can expose your endpoints on different hosts or paths.
 
````yaml
 graphql:
     endpoints:
         admin:
             path: /admin
         another:
             host: ^another\.example\.com    
         frontend:
             path: /
 ````

>>> The order of endpoints matter, endpoints works like routes, 
and the first endpoint matching with the given criteria and the incoming request will be used.

# Usage

Once all your endpoints are defined you need to define object and operations available or not for
these endpoints. To do this you must use the `@Endpoints` annotation in each definition you want to
control. This annotation must be used inside the `options` property of each definition

````php
<?php

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * ...
 * @GraphQL\MutationAdd(options={
 *     @GraphQL\Plugin\Endpoints("admin")
 * })
 */
class User implements NodeInterface
{
````
In the above example the mutation `addUser` is limited to `admin` endpoint and will be hidden
for users not using that endpoint. 

Can restrict entire object and all related operations only for some endpoint.

````php
<?php

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * @GraphQL\ObjectType(options={
 *     @GraphQL\Plugin\Endpoints({"admin"})
 * })
 */
class User implements NodeInterface
{
````

In the above example `User` is restricted to `admin` endpoint. 
All operations, fields etc related to this type will be automatically hidden in the schema.

# Alias

Can use alias to define multiple endpoints without the need to define all of them every time.

For example, if you have two admin endpoints, `admin_user` and `admin_system`, maybe
you need the type `User` in all these admins, but operations like `addUser` or `removeUser`
should be restricted to the `admin_system` endpoint. 
In the other hand the `User` should not be available in `frontend` for non authenticated users.

Firstly configure your endpoints and aliases:

````yaml
graphql:
    endpoints:
        admin_user:
            roles: [IS_AUTHENTICATED_FULLY, ROLE_USER]
        admin_system:
            roles: [IS_AUTHENTICATED_FULLY, ROLE_ADMIN]
        frontend:
            roles: IS_AUTHENTICATED_ANONYMOUSLY
    endpoint_alias: 
        admin: [admin_user, admin_system]  
 ````

The `admin` alias references to `admin_user` and `admin_system` endpoints, 
now you can use this alias like a real endpoint name in any place.

````php
<?php

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * ...
 * 
 * @GraphQL\ObjectType(options={
 *     @GraphQL\Plugin\Endpoints({"admin"})
 * })
 * @GraphQL\QueryList(options={
 *     @GraphQL\Plugin\Endpoints({"admin_system"})
 * })
 * @GraphQL\MutationAdd(options={
 *     @GraphQL\Plugin\Endpoints({"admin_system"})
 * })
 * @GraphQL\MutationUpdate(options={
 *     @GraphQL\Plugin\Endpoints({"admin_system"})
 * })
 * @GraphQL\MutationDelete(options={
 *     @GraphQL\Plugin\Endpoints({"admin_system"})
 * })
 */
class User implements NodeInterface
{
````
In the above example the `User` object is allowed using the alias `admin` in all admin endpoints. 
All fields and relations in other objects related to `User` are allowed, 
but CRUD operations require the endpoint `admin_system`. 

# Default Endpoint

By default all objects and operations are exposed unless specific endpoints are defined for each one.
Can change this behavior defining a default endpoint.

Define a default endpoint in your `congig.yml`

````yaml
graphql:
    endpoints:
        admin_user:
            roles: [IS_AUTHENTICATED_FULLY, ROLE_USER]
        admin_system:
            roles: [IS_AUTHENTICATED_FULLY, ROLE_ADMIN]
        frontend:
            roles: IS_AUTHENTICATED_ANONYMOUSLY
    endpoint_alias: 
        admin: [admin_user, admin_system]
    endpoint_default: admin  
 ````
 
In the above example the alias `admin` has been configured 
as default endpoint for all operations and objects. 
All operations and object without specific `endpoints` only will be available in
`admin_user` and `admin_system` endpoints.

>>> Default endpoint can works as a security layer to avoid expose objects and operations
accidentally. Is recommended always define the most restricted endpoint as your
default endpoint.