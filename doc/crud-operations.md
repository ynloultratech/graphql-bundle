# CRUD Operations

GraphQLBundle come with a basic but powerful CRUD operations to manage nodes.

## LIST

The **list** operation is used to fetch multiple nodes from database

To enable this operation must add the `CRUDOperations` annotation to the entity with **"list"** included.

````php
use Doctrine\ORM\Mapping as ORM;
use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @GraphQL\ObjectType()
 * @GraphQL\CRUDOperations(include={"list"})
 */
class User
{
....
````
Now yo can view a new available query in the GraphiQL explorer to request a paginated list of users.

###### Example Query:
````graphql
query {
  users(first: 5) {
    edges {
      node {
        username
      }
    }
  }
}
````
For more details and information about cursor based pagination, 
Edges & Nodes refer to the following [GraphQL Documentation](http://graphql.org/learn/pagination/#pagination-and-edges) 
or [Relay Specification](https://facebook.github.io/relay/graphql/connections.htm)

## ADD

The add operation is a [mutation](https://facebook.github.io/relay/graphql/mutations.htm) to add new nodes of current type.

To enable this operation must set **"add"** to the list of operations included in the `CRUDOperations` annotation.

````php
use Doctrine\ORM\Mapping as ORM;
use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @GraphQL\ObjectType()
 * @GraphQL\CRUDOperations(include={"list", "add"})
 */
class User
{
....
````

The *add* operation needs a extra configuration before use, 
any add operation need at least the information to add, 
in this case for example to add a new User is required at least the `username`, `email` and `password`

GraphQLBundle use the power of Symfony Forms to create 
the [Input](https://facebook.github.io/relay/graphql/mutations.htm#sec-Mutation-inputs) argument for mutations.

### AddUserInput FORM

GraphQLBundle use naming convention for many tasks 
in order to avoid many unnecessary configurations. It's not magic, :) *Convention over configuration*

By default the add operation is named with the name of the node,
in this case `User`, prefixed with `add`. In this case the operation name is `addUser`.

All mutations forms should be placed under the folder `Form/Input` using the following
naming convention:

Mutations Forms:
 
 `Form/Input/{Node}/{OperationName}Input`

Then must create the following form class:

````php
namespace AppBundle\Form\Input\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\User;

class AddUserInput extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, ['required' => true])
            ->add('email', null, ['required' => true])
            ->add('password', null, ['required' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
            ]
        );
    }
}
````

> The "required" option is used to set some fields as required in the GraphQL schema, 
for more powerful validation must use [validations constraints](mutations/input-validation.md) of Symfony Validator.

Now you are ready to start adding users to your database using the GraqphQL API.

###### Example Mutation:

````graphql
mutation addUser($user: AddUserInput!) {
  addUser(input: $user) {
    node {
      id
      username
      email
      password
    }
  }
}
````
Variables:
````json
{
  "user": {
    "username": "admin",
    "email": "admin@example.com",
    "password": "1234"
  }
}
````

## UPDATE

The **update** operation is similar to the **add** operation, 
to enable this operation must set **"update"** to 
the list of operations included in the `CRUDOperations` annotation.

Like **add** operation the **update** require a form to enter the data to modify.
 
### UpdateUserInput FORM

Some times the update form is very similar to the add form, many times not. 
To create the update form must follow the same naming convention of the **add** operation form.

In this case the form should be named `UpdateUserInput`

The following example shows how create a update form using inheritance with the previously created **AddUserInput** form.

````php
namespace AppBundle\Form\Input\User;

use Symfony\Component\Form\FormBuilderInterface;

class UpdateUserInput extends AddUserInput
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id');
        parent::buildForm($builder, $options);
    }
}
````
In this case the `id` field has been added to know which record should be modified.

### Using the same FORM for Add & Update

Like the example above the update & add forms are very similar many times, 
to avoid create two different form classes to 
these scenarios you can reuse the same form using the following approach.

Create a form only with the name of the Node 
followed by the input prefix, in this case `UserInput`.

````php
namespace AppBundle\Form\Input\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\User;

class UserInput extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['operation'] === 'updateUser') {
            $builder->add('id', null, ['required' => true]);
        }

         $builder
               ->add('username', null, ['required' => true])
               ->add('email', null, ['required' => true])
               ->add('password', null, ['required' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'operation' => null,
            ]
        );
    }
}
````

Note the new added option `operation` in the form, this option is **required** to allow this use case,
because the form will be configured with the name of the operation executed.
 
## DELETE
 
The delete operation is simply, 
the only that you need is set **"delete"** to the list 
of operations included in the `CRUDOperations` annotation.
 
 ````php
 /**
  * @ORM\Entity()
  * @ORM\Table()
  *
  * @GraphQL\ObjectType()
  * @GraphQL\CRUDOperations(include={"list", "add", "update", "delete"})
  */
 class User implements NodeInterface
 {
 ````
 
## Excluding Operations
 
By default the use of `@GraphQL\CRUDOperations()` include all 
operations to the node where the annotation is used.
 
You can include all operations that you need using the `include` option:
 
`@GraphQL\CRUDOperations(include={"list", "add", "update"})`
 
or exclude those that you don't need:

`@GraphQL\CRUDOperations(exclude={"delete"})`

## Where are GET operation?

Fetch a simple node is a global operation and is not required add this operation to every node,
can use `node(id)` or `nodes(ids)`. 
In the other hand if you need retrieve a node using another field,
for example, get a User by username, in this case can create a custom [Query](queries.md).

## Customizing

CRUD operations are simple and powerful and you can customize,
 see the [CRUD Customizing documentation](crud-operations/customizing.md)
