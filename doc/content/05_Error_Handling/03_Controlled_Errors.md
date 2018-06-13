Any application, from simple to complex, can have its fair share of errors. 
It is important to handle these errors and when possible, report these errors back to your users 
for information. It's a best and recommended practice, keep all errors controlled and a record with information 
of all possible errors and his description.

# What is a controlled error?

Is error that you know and commonly is part of your application logic.

### Examples:

- The account does not have sufficient funds to do this.
- The user account has unconfirmed email.
- You cannot report yourself for spam.
- You attempted to reply to a message that is deleted or not visible to you.

# Why are important controlled errors?

Commonly API consumers must need parse these errors and show the appropriate
action or message to final users.

For example, when your API return a error like `The account does not have sufficient funds to do this` 
the UI must display some advise and link to add funds to your account.
The UI should not parse the message because the message can change over the time, 
at this point is required use a error code. While the text for an error message may change, 
the codes will stay the same.

### Examples:
- **1001:** The account does not have sufficient funds to do this.
- **1002:** The user account has unconfirmed email.
- **1003:** You cannot report yourself for spam.
- **1004:** You attempted to reply to a message that is deleted or not visible to you.

Is easier for API consumers when know all possible errors and his code to
take appropriate actions; display a error message to final user, do a redirect or
any other action.

>>> Error handling on clients should be done using only the Error Codes. 
The Description string should be subject to change without prior notice.

# Create and Use Controlled Errors

Yo can register you errors in the bundle configuration:

````
graphql:
    error_handling:
      controlled_errors:
          map:
            1101:
              message: Insufficient funds
              description: The account does not have sufficient funds to do the requested operation.
````
or create a exception implementing `Ynlo\GraphQLBundle\Exception\ControlledErrorInterface` or
 extends from `Ynlo\GraphQLBundle\Exception\ControlledError`.

````php
<?php
namespace App\Exception;

use Ynlo\GraphQLBundle\Exception\ControlledError;

class InsufficientFunds extends ControlledError
{
    protected $code = 1101;

    protected $message = 'Insufficient funds';

    protected $description = 'The account does not have sufficient funds to do the requested operation.';
}
````
Now in your resolver or any place inside your application logic:

````php
...
if ($user->getBalance() < $order->getAmount()) {
  throw new InsufficientFunds();
  //or
  throw ControlledError::create(1101);
}
````

Then, your API consumers receive a error like this:

````json
{
  "errors": [
    {
      "code": 1101,
      "message": "Insufficient funds",
      "category": "user"
    }
  ]
}
````

> The description is only for documentation purposes, and is recommended if you want to export this list of errors
to keep your error codes in the documentation up to date.

# Keep yours errors documented

A important thing of controlled errors is share with API consumers, 
is very important that your clients have a idea of all possible errors before the error happen.
The best way to do this is share the list of all errors in your API documentation.
This is a tedious task and one of the main reasons why many developers avoid using controlled errors.
To manage this task GraphQLBundle comes with a set of tools that make this task up fun.

To check the list of all controlled errors can execute the following command:

    bin/console graphql:error:list

Output:
````
+------+-----------------------+----------------------------------------------------------------------------------------------------------------------------------------------+
| Code | Text                  | Description                                                                                                                                  |
+------+-----------------------+----------------------------------------------------------------------------------------------------------------------------------------------+
| 400  | Bad Request           | The request was invalid or cannot be otherwise served.                                                                                       |
| 401  | Unauthorized          | Missing or incorrect authentication credentials. This may also returned in other undefined circumstances.                                    |
| 403  | Forbidden             | The request is understood, but it has been refused or access is not allowed.                                                                 |
| 404  | Not Found             | The object requested is invalid or does not exist.                                                                                           |
| 500  | Internal Server Error | Something is broken. This is usually a temporary error, for example in a high load situation or if an endpoint is temporarily having issues. |
| 1101 | Insufficient funds    | The account does not have sufficient funds to do the requested operation.                                                                    |
+------+-----------------------+----------------------------------------------------------------------------------------------------------------------------------------------+
````
As you can see, some are common errors and the code corresponds with the HTTP status code, but this is only for
your convenience, because in almost all responses in graphQL the real HTTP status is always 200. The first errors
comes by default with GraphQLBundle and are always exposed, the last one is the previously created `InsufficientFunds`
error.

Because GraphQLBundle comes with some default error codes, and to avoid confusion with HTTP status codes we recommend
start your enumeration using `1000` and use a consecutive number for each error.
Anyway you can do it as it suits your needs, the only thing you should have in mind is that no error code can be repeated.

## Export your error list

The main purpose of the list of errors is to be able to export it to add it to your documentation. The previously mentioned command has the possibility to export that list to a file using different formats.

    bin/console graphql:error:list --output=error_codes.md --exporter=markdown
    
The above command generate a file `error_codes.md` with:  

````markdown
    
| Code | Text                  | Description                                                                                                                                  |
| ---  | ---                   | ---                                                                                                                                          |
| 400  | Bad Request           | The request was invalid or cannot be otherwise served.                                                                                       |
| 401  | Unauthorized          | Missing or incorrect authentication credentials. This may also returned in other undefined circumstances.                                    |
| 403  | Forbidden             | The request is understood, but it has been refused or access is not allowed.                                                                 |
| 404  | Not Found             | The object requested is invalid or does not exist.                                                                                           |
| 500  | Internal Server Error | Something is broken. This is usually a temporary error, for example in a high load situation or if an endpoint is temporarily having issues. |
| 1101 | Insufficient funds    | The account does not have sufficient funds to do the requested operation.                                                                    |
    
````

Currently the following exporters are available:

- **console** (default): Pretty table to view and check in console
- **markdown** : Table in markdown format, ready to add to any markdown based documentation.

> You can create your own exporter implementing `ErrorListExporterInterface` and registering as a service with this tag: `graphql.error_list_exporter`. If you
have service autowiring enabled the service is automatically registered as exporter without the need of add the tag.

Example:

````php
<?php

namespace App\Error\Exporter;

use Symfony\Component\Console\Output\OutputInterface;
use Ynlo\GraphQLBundle\Error\Exporter\MarkdownTableExporter;

class MyCustomErrorExporter extends MarkdownTableExporter
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'custom';
    }

    /**
     * @inheritDoc
     */
    public function export($errors, OutputInterface $output): void
    {
        $output->writeln(
            <<<MARKDOWN
A API operation can return multiple error and warning codes. 
Error codes and associated messages are sorted in ascending order by error code number.

> While the text for an error message may change, the codes will stay the same. 
Error handling on clients should be done using **ONLY** the Error Codes. 
The message string should be subject to change without prior notice.

MARKDOWN
        );
        
        $output->writeln('<div class="error-table">');
        parent::export($errors, $output);
        $output->writeln('</div>');
    }
}
````
The above custom exporter extends from markdown exporter to add custom header, 
and wrap the error table inside a div with `error-table` class.
