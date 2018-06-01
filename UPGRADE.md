# Upgrade to 1.1

## **BC BREAK:** Removed prefix `is` and `has` on methods without explicit name.

This is a **IMPORTANT** change, you have to update your definitions. 
Before `v1.1` all methods with prefix `is` and `has`, commonly boolean fields, 
are converted to field names as is. Now that prefix is removed.

Before:

- `isActive()` => `isActive`
- `hasSomethingToDo()` => `hasSomethingToDo`

After:

- `isActive()` => `active`
- `hasSomethingToDo()` => `somethingToDo`

> This change only affect interfaces and methods if the field annotation does not have a field
name manually configured.

The solution is set manually the name with the prefix in all existent affected methods in order to 
keep your API functional.

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

## **Minor BC BREAK:** Internal GraphQL definitions extension has been renamed to plugins

- namespaces: `Ynlo\GraphQLBundle\Definition\Plugin` => `Ynlo\GraphQLBundle\Definition\Plugin`
- tag: `graphql.definition_extension` => `graphql.definition_plugin`

Update this in your project if you have custom GraphQL extensions.


