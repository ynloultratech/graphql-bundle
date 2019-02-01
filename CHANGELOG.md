v1.2.8 - Unreleased
----
 *  Added support to use endpoints with forms
 *  LexikJWTGraphiQLAuthenticator now trigger a event `lexik_jwt_authentication.on_authentication_success`
 *  Fix bug with endpoints and inherited fields from interfaces

v1.2.7 - 2019-01-30
----
 * Fix bug with LexikJWTGraphiQLAuthenticator when username does not exist
 * Update LexikJWTGraphiQLAuthenticator constructor to support auto wiring
 * Fix a bug when try to guess a form type for types with modifiers
 
v1.2.6 - 2019-01-14
----
 * Now invalid types or malformed graphql requests are displayed as client errors (400 Bad Request)
 * Fix documentation for `NodeComparisonExpression`
 * Fix issue with validation and "children" in property path in symfony form ^4.1

v1.2.5 - 2018-08-6
----
 * Fixed #12 Missing pagination `parentField` when set `@Pagination` annotation on existent field (EXTRA_LAZY)
 * Fixed invalid `operation` name in forms when use namespace alias

v1.2.4 - 2018-08-03
----
 * Fixed can't disable specific fields on `QueryList.orderBy`
 
v1.2.3 - 2018-08-01
----
 * Fix exception in `cleanUp` plugin when some definition is empty
 * Added support to filter nodes using empty array to select records without relation
 
v1.2.2 - 2018-07-25
----
 * Added support to use collection filters for interfaces (polymorphic nodes)
 * Added support to set filters type with modifiers `[]` or `!` 
 * Added support to use page based pagination
 * Fixed issue checking permissions on specific objects
 * Fixed fields from interface use same endpoints as interface definition
 * Fixed issue in lists not ordering correctly parent fields
 * Fixed issue when use @Pagination to customize @QueryList options
 * Fixed custom filter description does not appear in schema
 * Fixed resolve correctly extensions related to interface definitions
 * Added support to use Xdebug to debug queries using the GraphiQL API Explorer
  
v1.2.1 - 2018-07-16
----
 * Added `CollectionComparisonOperator` as default comparison operator for array and enums
 * Added support to use custom namespaces for queries and mutations
 * Added support to use `alias` to customize name for namespaced operations
 * Allow set custom type name and description for mutations `input` argument
 * Added `plural` in constraint violation error for pluralizing the violation message
 * Fixed check only forbidden type if the operation does not have any endpoint configured
 * Added behat step to verify GraphQL error code
 * Added behat step to verify not existing row in table
 * Added support to use endpoints and others options in arguments
 * minors bugs fixed
  
v1.2.0 - 2018-07-09
----
 * Some minors bugs fixed
 * Added input type guesser to guess the correctness GraphQL type in forms fields [#10](https://github.com/ynloultratech/graphql-bundle/issues/10)
 * Removed experimental `roles` and role AuthorizationChecker.
 * Added `Date` type using *ISO-8601* format: 2018-06-21
 * Added `Time` type using *ISO-8601* format: 13:18:05
 * Fixed Private properties marked as @Field from parent objects are not correctly inherited
 * Added `where` in lists to support filters with advanced settings
 * Deprecated `filter` in list pagination in favor of more advanced `where` field
 * Added `graphql.operationStart` and `graphql.operationEnd` events
 * Added `order` as better alternative to sort collections of nodes
 * Deprecated `orderBy` in collections in favor of `order` which uses enum for field names
 * Fixed inherited interface properties override object properties
 * Improved list search and allow configure `searchFields` in QueryList annotation 
 
v1.1.3 - 2018-07-03
----
 * Fixed API endpoint route to allow OPTIONS method (required by GraphiQL and other tools).

v1.1.2 - 2018-06-27
----
 * Handle symfony HTTP exceptions to display related status code error instead of "Unknown error"
 * Added build-in LexikJWT authentication for GraphiQL API explorer.
 * Deprecated the old `jwt` authentication in favor of the new one `lexik_jwt`
 * Removed `demo` app inside the project, moved out to another repository `ynloultratech/graphql-bundle-demo`.
 * Removed `docs` inside the project, moved out to another repository `ynloultratech/graphql-bundle-docs`
 * Fixed API endpoint route to use POST method.

v1.1.1 - 2018-06-21
----
 * Added `alternative_id` option to ID form type to allow find nodes using alternatives columns.

v1.1.0 - 2018-06-18
-----
 * Added `symfony4` support
 * Fixed error in pagination when filters does not have any field to filter
 * Fixed error in pagination when a node use custom object as node field
 * Added support to use abstract PHP classes as graphql interfaces
 * Added graphql scalar type called `Any` to support arbitrary values
 * Added graphql form extension to allow set custom type, description and deprecationReason in forms
 * **[BC BREAK]** `has` and `his` prefixes are now removed in method definitions like `get` and `set`. Now a method like `isActive()` is converted to graphql definition like `active`
 * Exception is thrown when register a graphql custom type and is not instantiable
 * Added graphql scalar type called `DynamicObject` to support custom objects like `key:value` pairs
 * Hide field description in graphiql when a list of fields are displayed _(improve readability)_
 * Update `graphiql` assets to latest version
 * Fixed [#11](https://github.com/ynloultratech/graphql-bundle/issues/11) (Label=false in a form, throws schema error ...Must be named. Unexpected name: (empty string))
 * Fixed error when a validation constraint does not have code or message template
 * Fixed log errors correctly and define user errors as notices
 * Resolve mutation payload class automatically for easy override
 * Added support tu use CRUD extensions for real PHP interfaces without register a graphql interface type.
 * Removed `getPriority` method in CRUD extensions, must use service tag priority instead.
 * Add config to set custom labels for GraphiQL JWT Authentication form fields
 * Fixed GraphiQL CORS error when use the `explorer` in a different domain or subdomain
 * **[BC BREAK]** Change definitions "extensions" to "plugins" to avoid confusion with CRUD extensions
 * Added dataCollector to display helpful information in the web profiler
 * Added support to configure plugins using annotations, arrays are deprecated
 * Added support to use endpoints
 * Improve the schema cache warmer for dev environments
 * PHPUnit ApiTestCase has been deprecated in favor of Behat tests
 * Added support to use JWT authentication on Behat tests
 * Added GraphQL event system to hook into field/operations before/after are resolved
 * Added AccessControl plugin to control access to object, fields and operations using Symfony security checker with expressions
 * Fixed deprecation adviser on behat tests to display deprecation warnings correctly
 * Added IDEncoder utility and support for custom ID Encoders
 * Deprecated class `ID`, use IDEncoder utility to encode/decode globalId and nodes
 * Added support to define multiple types in the same class and filter fields using `in` and `notIn`
 * Added support for polymorphic objects, creating interface and different object in the same or different classes
 * Added support to use custom `node` and/or `bundle` namespace in definitions
 * Added schema validation plugin to validate schema during compilation time
 * Added support to ignore implemented interfaces on objects and child interfaces
 * Added support to load symfony4 data fixtures in path 'src/DataFixtures'
 * Added command to export schema to standard output or file, as json or graphql format
 * Added support to use custom routes for all or specific scenarios on behat tests
 * Added clean-up definition plugin in order to remove non used definitions of each endpoint
 * **[BC BREAK]** Moved plugins configuration out of `definitions`
 * Added option to set custom favicon in the GraphiQL Explorer
 * Added option to set custom API documentation link in the GraphiQL Explorer
 * Fixed max limit reached when use a field with limited concurrent usage in tests
 * Added `snapshot` feature tests to verify the schema stability
 * Fixed missing operation when namespace has been disabled for that operation
 * Added `default_query` config to define custom default query when load the explorer
 * Fixed duplicate field definition when interface publish a field with different name
 * Improve error handling, Add controlled errors, custom error formatter and handlers
 * Added support to display validation messages as errors
 * Deprecated internal `TaggedServices` component in favor of symfony injection using "!tagged tag_name"
 * Added support to listen LexikJWT authentication failures and display formatted GraphQL errors
 * Fixed incorrect error format when error happen in schema or out of resolver

v1.0.6 - 2018-03-23
----
 * Resolve array of IDs to real nodes
 * Added `MutationDeleteBatch` to allow delete multiple nodes in batch

v1.0.5 - 2018-03-22
----
 * Allow doctrine entities not implementing node interface
 * Mutations can override and use custom initial form data

v1.0.4 - 2018-03-21
----
 * Automatically resolve parameters of type ID to real node

v1.0.3 - 2018-03-20
----
 * Added support for mutations to return array of objects

v1.0.2 - 2018-03-19
----
 * Added support for doctrine GUID type
 * Added request middleware interface to allow customize API requests
 * Added support to upload files using [GraphQL multipart request specification](https://github.com/jaydenseric/graphql-multipart-request-spec)

v1.0.1 - 2018-02-08
-----
 * Initial Release
