# Routes

By default behat tests send all queries to root `/` url of your project, if your api
use another path or hostname, must configure the route to use in tests:

`behat.yml`

````yaml
 default:
     extensions:
         Ynlo\GraphQLBundle\Behat\GraphQLApiExtension:
             route: api_root
````
 
With the above configuration our behat extension use symfony router to create the path to your api endpoint using the given route name.

You can configure different route for specific scenarios using tags:

````
@route:front_endpoint
Feature: Post
  Scenario: Add Post
    Given the operation named "AddPost"
    ...    
````
 

# JWT Authentication

If your API require JWT authentication, can use tags to define the username of the user to generate the token.

````
@jwt:admin
Feature: Post
  Scenario: Add Post
    Given the operation named "AddPost"
    ...    
````

> By default the authentication works with FosUserBundle and LexikJWTAuthenticationBundle.

If you need a custom token generator or another way to resolve the user
must create your own resolver and token generator.

````yaml
default:
    extensions:
        Ynlo\GraphQLBundle\Behat\GraphQLApiExtension:
            authentication
                jwt:
                  user_resolver: App\Behat\ResolveUserByEmail
                  generator: App\Behat\OAuth2TokenGenerator
              
````

- Resolver: Ynlo\GraphQLBundle\Behat\Authentication\UserResolverInterface
- Generator: Ynlo\GraphQLBundle\Behat\Authentication\JWT\TokenGeneratorInterface
