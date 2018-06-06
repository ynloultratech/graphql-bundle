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

If your API require JWT authentication must configure the 
behat extension to generate a token before each feature is executed.

`behat.yml`
````yaml
default:
    extensions:
        Ynlo\GraphQLBundle\Behat\GraphQLApiExtension:
            authentication:
                jwt:
                    users: [admin, customer]
````

After this you can use `tags` in features with the username to use. The prefix `user:` must be used to identify the type of tag.

````
@user:admin
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
                  users: [admin, customer]
                  user_resolver: App\Behat\ResolveUserByEmail
                  generator: App\Behat\OAuth2TokenGenerator
              
````

- Resolver: Ynlo\GraphQLBundle\Behat\Authentication\UserResolverInterface
- Generator: Ynlo\GraphQLBundle\Behat\Authentication\JWT\TokenGeneratorInterface
