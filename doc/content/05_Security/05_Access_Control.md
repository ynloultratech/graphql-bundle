You can leverage all security features provided by the 
[Symfony Security component](http://symfony.com/doc/current/book/security.html).
If you wish to restrict the access of some endpoints, you can use 
[access controls directives](http://symfony.com/doc/current/book/security.html#securing-url-patterns-access-control).
You can add security through [Symfony's access control expressions](https://symfony.com/doc/current/expressions.html#security-complex-access-controls-with-expressions) 
in your objects and fields.

Here is an example:

````php
<?php

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * ...
 * @GraphQL\MutationAdd(options={
 *     @GraphQL\Plugin\AccessControl("has_role('ROLE_ADMIN') or object == user")
 * })
 */
class User implements NodeInterface
{
````

The above example allow only the access to a `User` object if the logged user is
admin or is his record.

The same approach can be used for fields or operations.

Field:
````php
/**
 * ...
 * @GraphQL\Field(options={
 *     @GraphQL\Plugin\AccessControl("has_role('ROLE_ADMIN') or object.author == user")
 * })
 */
protected $author;
````

Operation:
````php
 * ...
 * @GraphQL\MutationAdd(options={
 *     @GraphQL\Plugin\AccessControl(
 *     expression="has_role('ROLE_ADMIN') or has_role('ROLE_BLOGGER')",
 *     message="Does not have enough permissions tu publish new posts.")
 * })
 */
class Post implements NodeInterface
{
````

>> If unauthorized access is detected a exception is thrown and the consumer view a **security** error. 
In this case the query fails and not data is returned in any way.

The following functions and variables are available:

- **is_anonymous():** check if current user is authenticated anonymously.
- **is_fully_authenticated():** check if current user is fully authenticated.
- **has_role():** check if user has the given role
- **object:** current object where the expression is executed
- **subject:** current object where the expression is executed

## Custom Message

By default when API request will be denied you will get the predefined message.
You can change it by configuring "message" attribute.

For Example:

````php
<?php

use Ynlo\GraphQLBundle\Annotation as GraphQL;
use Ynlo\GraphQLBundle\Model\NodeInterface;

/**
 * ...
 * @GraphQL\MutationAdd(options={
 *     @GraphQL\Plugin\AccessControl(
 *           expression="has_role('ROLE_ADMIN') or object.author == user",
 *           message="Sorry, but you are not the owner."
 *           )
 * })
 */
class Book implements NodeInterface
{
````

>>> Access control does not hide object, fields or operations, only restrict the access.
The GraphQL schema still displaying all these definitions.
In order to hide definitions based on user roles must use [endpoints](../07_Advanced/03_Endpoints.md).