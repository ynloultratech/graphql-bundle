Since `v1.1` GraphQLBundle display validation errors like others errors.

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

Unlike other errors validation errors contains the key `constraintViolations`
containing a array of violations.

## Violation:

- **code:** Machine-digestible error code for the violation.
- **message:** Violation message
- **messageTemplate:** Raw violation message.
    The raw violation message contains placeholders for the parameters returned by parameters.
    Typically you'll pass the message template and parameters to a translation engine.
- **propertyPath:** Field path causing the issue relative to input, if blank is a form general error
- **parameters:** Parameters to be inserted into the raw violation message.
- **invalidValue:** Value that caused the violation.
  