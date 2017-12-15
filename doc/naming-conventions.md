# Naming Conventions

GraphQLBundle use naming convention for many tasks 
in order to avoid many unnecessary configurations.

## Files Locations

Some files require special locations to works without specific configuration

- **Queries:**  `Query\{Node}\{QueryName}`
- **Fields:**  `Query\{Node}\Field\{FieldName}`
- **Mutations:**  `Mutation\{Node}\{MutationName}`
- **Input Forms:**  `Form\Input\{Node}\{MutationName}Input`
- **Types:**  `Type\{Name}Type`
- **Extensions:**  `Extensions\{InterfaceName}Extension`

> `{Node}` refer to the public node name and not the class name, 
if the class name is `User` and the public name is `Customer` the node name is the last one.



