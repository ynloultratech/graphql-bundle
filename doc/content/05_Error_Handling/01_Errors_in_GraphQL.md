During the query execution process all errors are caught and collected, 
this process  never throws exceptions. After execution, they are available in `errors` as array in the response.

````json
{
  "errors": [
    {
      "code": 1234,
      "message": "Some Error Happen",
      "category": "user"
    },
    {
      "code": 5678,
      "message": "Another Error Happen",
      "category": "user"
     }
  ]
}
````

GraphQL’s ability to send both data and errors is nothing short of amazing. 
<div class="graphiql">
<div class="request">

````graphql
query node(id: "UG9zdDox"){
    ... on Post {
        title
        author {
            login
            email
        }
    }
}
````

</div>
<div class="response">

````json
{
  "errors": [
    {
      "code": 403,
      "tracking_id": "8AC624CA-F2BC-4F23-6A7F-28F0AC8B",
      "message": "You are not authorized to view this user email.",
      "category": "user",
      "locations": [
        {
          "line": 6,
          "column": 13
        }
      ],
      "path": [
        "node",
        "author"
      ]
    }
  ],
  "data": {
    "node": {
      "title": "Laudantium quod magni non voluptas fuga non autem non.",
      "author": null
    }
  }
}
````

</div>
</div>

> If GraphQL gives you a result with data, even if that result contains errors, it is not an error.

I don’t care if the result is `{data: {foo: null}}`. Data is data; any arbitrary nully 
logic implemented after GraphQL returns is just that: arbitrary.

Unfortunately, most client code boils down to this:

	if (result.errors) throw result.errors[0]

All these clients are ignoring that the server maybe return something usable. 
It’s like having a talk with a real human: _“Hey Matt, here are those results you wanted. 
I got you everything except that task field; I went to look it up, but it didn’t exist in your database.”_
With all this power, we could do some really cool things on the client!


