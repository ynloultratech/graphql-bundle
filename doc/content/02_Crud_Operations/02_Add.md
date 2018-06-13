The add operation is a [mutation](https://facebook.github.io/relay/graphql/mutations.htm) to add new nodes of current type.

To enable this operation must add `MutationAdd` annotation.

````php
use Doctrine\ORM\Mapping as ORM;
use Ynlo\GraphQLBundle\Annotation as GraphQL;

/**
 * @ORM\Entity()
 * @ORM\Table()
 *
 * @GraphQL\ObjectType()
 * @GraphQL\QueryList()
 * @GraphQL\MutationAdd()
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

# AddUserInput FORM

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
namespace App\Form\Input\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

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
for more powerful validation must use validations constraints of Symfony Validator.

Now you are ready to start adding users to your database using the GraqphQL API.

### Example Mutation:

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
#### Variables:
````json
{
  "user": {
    "username": "admin",
    "email": "admin@example.com",
    "password": "1234"
  }
}
````