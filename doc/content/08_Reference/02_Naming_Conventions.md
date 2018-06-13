# Naming Conventions

GraphQLBundle use naming convention for many tasks 
in order to avoid many unnecessary configurations.

## Files Locations

Some files require special locations to works without specific configuration

- **Queries:**  `Query\{Node}\{QueryName}` **e.g.** *Query\User\GetUserByUsername*
- **Fields:**  `Query\{Node}\Field\{FieldName}` **e.g.** *Query\User\Field\IsCurrentUser*
- **Mutations:**  `Mutation\{Node}\{MutationName}` **e.g.** *Mutation\User\AddUser*
- **MutationsPayload:**  `Mutation\{Node}\{MutationName}Payload` **e.g.** *Mutation\User\AddUserPayload*
- **Input Forms:**  `Form\Input\{Node}\{MutationName}Input` **e.g.** *Form\Input\User\AddUserInput*
- **Types:**  `Type\{Name}Type` **e.g.** *Type\OrderStatusType*
- **Extensions:**  `Extension\{InterfaceName}Extension` **e.g.** *Extension\HasAuthorExtension*

>> `{Node}` refer to the public node name and not the class name, 
if the class name is `User` and the public name is `Customer` the node name is the last one.



