>>> Please, before make a upgrade of your project to any version, read firstly how [updates in GraphQLBundle](https://graphql-bundle.ynloultratech.com/Advanced/Bundle_Upgrade.html) are made.

# UPGRADE FROM v1.1 to 1.2

>> **Heads up!** Upgrade to `v1.2` requires change some settings and definitions.
The following steps explain how migrate to this version and keep your API functional.
With all necessary adjustments this version has fully backward compatibility.

## **Update:** The `filters` argument in collections has been updated and renamed to `where`

In order to allow more advanced options to filter collections a new `where` option has been added, the old `filters` has been marked as deprecated and hidden by default.

Before:

````graphql
query post{
  posts {
    all(first: 10, filters: { title: "Lorem" }) {
      edges {
        cursor
        node {
          title
        }
      }
    }
  }
}
````

After:

````graphql
query post{
  posts {
    all(first: 10, where: { title: { op:CONTAINS, value: "Lorem"}}) {
      edges {
        cursor
        node {
          title
        }
      }
    }
  }
}
````

Read more about this new option in the [documentation](https://graphql-bundle.ynloultratech.com/Crud_Operations/List/Filters.html).

In order to keep BC with your API consumers and keep the old `filters` 
usable during some time must enabled the following config:

````yaml
graphql:
    bc:
      filters: true

````

## **Update:** The `orderBy` argument in collection has been updated and renamed to `order`

In order to use ENUM for field names in the `orderBy` of collections this option has been refactored and renamed to `order`.

Before:

````graphql
query post{
  posts {
    all(first: 10, orderBy: { field: "title" }) {
      edges {
        cursor
        node {
          title
        }
      }
    }
  }
}
````

After:

````graphql
query post{
  posts {
    all(first: 10, order: { field: title }) {
      edges {
        cursor
        node {
          title
        }
      }
    }
  }
}
````

In order to keep BC with your API consumers and keep the old `orderBy` 
usable during some time must enabled the following config:

````yaml
graphql:
    bc:
      orderBy: true

````

## **Deprecate:** The GraphiQL authenticator provider `jwt` has been deprecated.

This provider has been deprecated in favor of a new specific provider `lexik_jwt`. For other cases use custom provider.

Before:

````yaml
graphql:
    graphiql:
        authentication:
            provider:
                jwt:
                  login:
                      url: api_login
````

After:

````yaml
graphql:
    graphiql:
        authentication:
            provider:
                lexik_jwt:
                    user_provider: fos_userbundle
````

---

# UPGRADE FROM v1.0 to v1.1

>> **Heads up!** Upgrade to `v1.1` requires change some settings and definitions.
The following steps explain how migrate to this version and keep your API functional.
With all necessary adjustments this version has fully backward compatibility.

## **Update:** LexikJWT authentication failures are displayed using GraphQL error format

Before:

````json
{
  "code": 401,
  "message": "JWT Token not found"
}
````

After:

````json
{
  "errors": [
    {
      "code": 401,
      "tracking_id": "A133373E-5164-DC6C-75DF-377B8DA2",
      "message": "JWT Token not found",
      "category": "user"
    }
  ]
}
````

This change can affect directly your API clients, in order to make this change progressively in all
your clients must activate the following option:

````
graphql:
    error_handling:
        jwt_auth_failure_compatibility: true

````

The above option generate a response like this:

````json
{
  "code": 401,
  "message": "JWT Token not found",
  "errors": [
    {
      "code": 401,
      "tracking_id": "5981C154-3BC7-7640-0097-FB0C3EC5",
      "message": "JWT Token not found",
      "category": "user"
    }
  ]
}
````

>>> The option `jwt_auth_failure_compatibility` is temporal and will be removed in the next mayor release.
Migrate your clients to the new error format.

---
## **Update:** Removed prefix `is` and `has` on methods without explicit name.

This is a **IMPORTANT** change, you have to update your definitions.
Before `v1.1` all **METHODS** with prefix `is` and `has`, commonly boolean fields,
are converted to field names as is. Now that prefix is removed.

 Before:

- `isActive()` => `isActive`
- `hasSomethingToDo()` => `hasSomethingToDo`


After:

- `isActive()` => `active`
- `hasSomethingToDo()` => `somethingToDo`

The solution can be set manually the old name in all existent affected methods in order to
keep your API functional.

>>> This change only affect interfaces and methods if the field annotation does not have a field
name manually configured.

---
## **Deprecate:** Constraint violations in the mutation payload has been deprecated.

By default in `v1.1` all constraint violations are displayed as errors in the list of errors.

Before:

````json
{
  "data": {
    "posts": {
      "add": {
        "node": null,
        "constraintViolations": [
          {
            "code": "c1051bb4-d103-4f74-8988-acbcafc7fdc3",
            "message": "This value should not be blank.",
            "messageTemplate": "This value should not be blank.",
            "propertyPath": "title",
            "parameters": [
              {
                "name": "{{ value }}",
                "value": "null"
              }
            ],
            "invalidValue": null
          }
        ]
      }
    }
  }
}
````

After:

````json
{
  "errors": [
    {
      "code": 422,
      "tracking_id": "B6DEE59D-16F9-98F9-F086-ADC79148",
      "message": "Unprocessable Entity",
      "category": "user",
      "constraintViolations": [
        {
          "code": "c1051bb4-d103-4f74-8988-acbcafc7fdc3",
          "message": "This value should not be blank.",
          "messageTemplate": "This value should not be blank.",
          "propertyPath": "body",
          "parameters": {
            "{{ value }}": "null"
          },
          "invalidValue": null
        }
      ]
    }
  ],
  "data": {
    "posts": {
      "add": {
        "node": null
      }
    }
  }
}
````

In order to keep your API functional must change the following configuration to keep BC with your clients:

````yaml
graphql:
    error_handling:
        # Where should be displayed validation messages.
        validation_messages: ~ # One of "error"; "payload"; "both"
````

> Before `v1.1` the default is `payload`, now is `error`, we recommend start using `both` in order to
 migrate your clients to this new approach progressively.

>>> The option `validation_messages` is temporal and will be removed in `v2.0` when all validation
errors will be displayed always in the list of errors.

---
## **Update:** Plugins configuration has been moved out of `definitions`

In your `config.yaml` must change:

Before:

````yaml
graphql:
    definitions:
        extensions:
            pagination:
                limit: 100
````

After:

````yaml
graphql:
    pagination:
        limit: 100
````
---
## **Deprecate:** Use of array as config in annotations has been deprecated and will be removed in the next mayor release.

Change your definitions to use annotations to set advanced options instead of arrays

Before:

````
/**
 * @GraphQL\Field(
 *     type="[Post]",
 *     options={
 *          "pagination": {
 *              "parent_field": "categories",
 *              "parent_relation": "MANY_TO_MANY"
 *          }
 *     }
 * )
 */
````

After:

````
/**
 * @GraphQL\Field(
 *     type="[Post]",
 *     options={
 *        @GraphQL\Plugin\Pagination(
 *               parentField="categories",
 *               parentRelation="MANY_TO_MANY"
 *         )
 *     }
 * )
 */
````

---
## **Deprecate:** The annotation `CRUDOperations` has been deprecated

The use of `@CRUDOperations` annotation is deprecated and will be removed in v2.0

Before:

````
 *
 * @GraphQL\ObjectType()
 * @GraphQL\CRUDOperations(include={'list', 'add', 'update', 'delete'})
 */
class Post implements NodeInterface
{
````

After:

````
 *
 * @GraphQL\ObjectType()
 * @GraphQL\QueryList()
 * @GraphQL\MutationAdd()
 * @GraphQL\MutationUpdate()
 * @GraphQL\MutationDelete()
 */
class Post implements NodeInterface
{
````
---
## **Update:** Removed `getPriority` method in CRUD extensions

In order to prioritize CRUD extensions must use tags priorities.
Is recommended use [autowiring](http://symfony.com/doc/current/service_container/autowiring.html)
for all extensions and define priorities only when is needed.

````yml
App\Extension\:
    resource: '../src/Extension/*'
    tags: ['graphql.extension']

App\Extension\UserExtension:
    tags: ['graphql.extension', priority: 50]
````

> Since `v1.1` if you have autowiring configured all extensions are automatically registered.

---
## **Update:** Internal GraphQL definitions extension has been renamed to plugins

- namespaces: `Ynlo\GraphQLBundle\Definition\Plugin` => `Ynlo\GraphQLBundle\Definition\Plugin`
- tag: `graphql.definition_extension` => `graphql.definition_plugin`

Update this in your project if you have custom GraphQL extensions.