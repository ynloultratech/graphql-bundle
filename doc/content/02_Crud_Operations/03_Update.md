The **update** operation is similar to the **add** operation, 
to enable this operation must add `MutationUpdate` annotation.

Like **add** operation the **update** require a form to enter the data to modify.
 
# UpdateUserInput FORM

Some times the update form is very similar to the add form, many times not. 
To create the update form must follow the same naming convention of the **add** operation form.

In this case the form should be named `UpdateUserInput`

The following example shows how create a update form using inheritance with the previously created **AddUserInput** form.

````php
namespace App\Form\Input\User;

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

# Using the same FORM for Add & Update

Like the example above the update & add forms are very similar many times, 
to avoid create two different form classes to 
these scenarios you can reuse the same form using the following approach.

Create a form only with the name of the Node 
followed by the input prefix, in this case `UserInput`.

````php
namespace App\Form\Input\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

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