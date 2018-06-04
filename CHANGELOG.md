[1.1 - Unreleased]
-----
 * Add `symfony4` support
 * Fix error in pagination when filters does not have any field to filter
 * Fix error in pagination when a node use custom object as node field
 * Add support to use abstract PHP classes as graphql interfaces
 * Add graphql scalar type called `Any` to support arbitrary values
 * Add graphql form extension to allow set custom type, description and deprecationReason in forms
 * **[BC BREAK]** `has` and `his` prefixes are now removed in method definitions like `get` and `set`. Now a method like `isActive()` is converted to graphql definition like `active`
 * Exception is thrown when register a graphql custom type and is not instantiable
 * Add graphql scalar type called `DynamicObject` to support custom objects like `key:value` pairs
 * Hide field description in graphiql when a list of fields are displayed _(improve readability)_
 * Update `graphiql` assets to latest version
 * Fixed #11 (Label=false in a form, throws schema error ...Must be named. Unexpected name: (empty string))
 * Fix error when a validation constraint does not have code or message template
 * Fix log errors correctly and define user errors as notices
 * Resolve mutation payload class automatically for easy override
 * Add support tu use CRUD extensions for real PHP interfaces without register a graphql interface type.
 * Removed `getPriority` method in CRUD extensions, must use service tag priority instead. 
 * Add config to set custom labels for GraphiQL JWT Authentication form fields
 * fix GraphiQL CORS error when use the `explorer` in a different domain or subdomain
 * **[BC BREAK]** Change definitions "extensions" to "plugins" to avoid confusion with CRUD extensions
 * Add dataCollector to display helpful information in the web profiler
 * Add support to configure plugins using annotations, arrays are deprecated
 * Add support to use endpoints
 * Improve the schema cache warmer for dev environments
 * PHPUnit ApiTestCase has been deprecated in favor of Behat tests
 * Add support to use JWT authentication on Behat tests
 * Add GraphQL event system to hook into field/operations before/after are resolved
 * Add AccessControl plugin to control access to object, fields and operations using Symfony security checker with expressions
 * Fix deprecation adviser on behat tests to display deprecation warnings correctly
 * Added IDEncoder utility and support for custom ID Encoders
 * Deprecated class `ID`, use IDEncoder utility to encode/decode globalId and nodes
 
1.0.6 - 18-03-23
----
 * Resolve array of IDs to real nodes
 * Add `MutationDeleteBatch` to allow delete multiple nodes in batch
 
1.0.5 - 18-03-22
----
 * Allow doctrine entities not implementing node interface
 * Mutations can override and use custom initial form data
 
1.0.4 - 18-03-21
----
 * Automatically resolve parameters of type ID to real node
 
1.0.3 - 18-03-20
----
 * Add support for mutations to return array of objects
 
1.0.2 - 18-03-19
----
 * Add support for doctrine GUID type
 * Add request middleware interface to allow customize API requests
 * Add support to upload files using [GraphQL multipart request specification](https://github.com/jaydenseric/graphql-multipart-request-spec)
 
1.0.1 - 18-02-08
-----
 * Initial Release
