# Upgrade to 1.1

## **BC BREAK:** Removed prefix `is` and `has` on methods without explicit name.

This is a **IMPORTANT** change, you have to update your definitions. 
Before `v1.1` all **METHODS** with prefix `is` and `has`, commonly boolean fields, 
are converted to field names as is. Now that prefix is removed.

 Before:
      
- `isActive()` => `isActive`
- `hasSomethingToDo()` => `hasSomethingToDo`


After:

- `isActive()` => `active`
- `hasSomethingToDo()` => `somethingToDo`

>>> This change only affect interfaces and methods if the field annotation does not have a field
name manually configured.
    
The solution is set manually the name with the prefix in all existent affected methods in order to 
keep your API functional.

---
## **BC BREAK:** Constraint violations are displayed in the list of errors by default.

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

> Before `v1.1` the default is `payload` you can use that value or use `both` if you want to 
keep the old approach or migrate your clients to this new approach progressively.

>> Return constraint violations in the mutation payload, it will remain an option, just 
that keeping all types of errors in the same place becomes clearer and easier to use.

---
## **Minor BC BREAK:** Plugins configuration has been moved out of `definitions`

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
## **Minor BC BREAK:** Removed `getPriority` method in CRUD extensions

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

---
## **Minor BC BREAK:** Internal GraphQL definitions extension has been renamed to plugins

- namespaces: `Ynlo\GraphQLBundle\Definition\Plugin` => `Ynlo\GraphQLBundle\Definition\Plugin`
- tag: `graphql.definition_extension` => `graphql.definition_plugin`

Update this in your project if you have custom GraphQL extensions.

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

