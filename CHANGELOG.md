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
