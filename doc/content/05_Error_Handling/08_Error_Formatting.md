By default, each error entry is converted to an associative array with following structure:

````
{
  "errors": [
    {
      "code": 403,
      "tracking_id": "84451079-A62A-4491-1F04-3E831BF5",
      "message": "Forbidden",
      "category": "user",
      "locations": [
        {
          "line": 2,
          "column": 3
        }
      ],
      "path": [
        "viewer"
      ],
      'trace' => [
             /* Formatted original exception trace */
      ]
    }
  ]
}
````

* **code:** Internal error code for [controlled errors](03_Controlled_Errors.md), or 500 for uncontrolled errors
* **tracking_id:** Internal code tracking_id ([see debugging](09_Debugging.md))
* **message:** Short and descriptive error text.
* **category:** Category that the error belongs to, by default exists three categories and are enough 
for almost all scenarios:
    * **internal:** For internal server errors, commonly uncaught exceptions. 
    This type of error commonly require a fix in the server side.
    * **user:** Client aware errors, commonly application logic errors. 
    Is recommended use [Controlled Errors](03_Controlled_Errors.md) for these errors.
    * **graphql:** The query fails due to GraphQL internal validation (syntax, schema logic, etc.).
    These type of error commonly require a fix in the API consumer.
* **locations:** points to a character in query string which caused the error. 
    In some cases (like deep fragment fields) locations will include several entries to track
     down the path to field with the error in query.
* **path:** Entry at key `path` exists only for errors caused by exceptions thrown in resolvers. 
    It contains a path from the very root field to actual field value producing an error 
    (including indexes for list types and field names for composite types).
* **trace:** During development or debugging this key is used to display the entire
exception trace.

# Custom Error Handling and Formatting

It is possible to define custom formatter and handler for result errors.

Formatter is responsible for converting instances of `GraphQL\Error\Error` to an array. 
Handler is useful for error filtering and logging.

To configure your custom handler or formatter define your services names in the config.

config.yml
````yaml
graphql:
    error_handling:
        formatter:            Ynlo\GraphQLBundle\Error\DefaultErrorFormatter
        handler:              Ynlo\GraphQLBundle\Error\DefaultErrorHandler
````