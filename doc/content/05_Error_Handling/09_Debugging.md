A good API error handling will allow developers to quickly find why, and how, they can fix a failed call. 
A bad API error handling will cause an increase in blood pressure, 
along with a high number of support tickets and wasted time.

In most cases, sending error messages to the client without supervision is a bad idea since 
those might leak sensitive information. For that reasons only [controlled errors](03_Controlled_Errors.md)
display the real message to final users, on the other hand uncaught exceptions always display `Internal Server Error`.

All are OK, but then, how can we know the error that actually happen in a certain request to be able to correct it?

This is where the `tracking_id` in the error response comes into play. Every time any exception is thrown
a unique `tracking_id` is created for that exception, this number is unique for that exception but can be
repeated every time the same exception is thrown in the same place with the same message.

The `tracking_id` is sent to API consumers in the response:

````json
{
  "errors": [
    {
      "code": 500,
      "tracking_id": "AD1AD4F8-ADA2-DE84-845D-3CA7E401",
      "message": "Internal server error",
      "category": "internal"
    }
  ]
}
````
As you can see, no sensitive information is leaked to the user at all. 
You might think this'll make bug reports less useful, but note how a UUID is attached to the error message!

Then, if you have installed and configured [Monolog](http://symfony.com/doc/current/logging.html) 
you are able to view the following error in your logs:

````
[2018-06-12 20:19:37] app.CRITICAL: (AD1AD4F8-ADA2-DE84-845D-3CA7E401) Notice: Undefined variable: user {"file":"/var/www/myapp/src/Query/User/Viewer.php","line":44,"error":"ErrorException","trace":"#original exception trace ....." }
````

Note how the same UUID `(AD1AD4F8-ADA2-DE84-845D-3CA7E401)` is sent to the user and 
logged together with the stack trace, making it easy to cross-reference user bug reports to your server logs.

## You must know:

- Errors when query fails due to GraphQL internal validation (syntax, schema logic, etc.). will
be treated as **DEBUG** message.
- User [controlled errors](03_Controlled_Errors.md) will be treated as **NOTICE**.
- Uncaught exceptions will be treated as **CRITICAL**

This is important because using the default logger configurations only **ERROR** and **CRITICAL** messages are logged
in production environments.