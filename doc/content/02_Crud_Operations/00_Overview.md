GraphQLBundle come with a basic but powerful CRUD operations to manage nodes.

- [List](01_List.md)
- [Add](02_Add.md)
- [Update](03_Update.md)
- [Delete](04_Delete.md)

# Where are GET operation?

Fetch a simple node is a global operation and is not required add this operation to every node,
can use `node(id)` or `nodes(ids)`. 
In the other hand if you need retrieve a node using another field,
for example, get a User by username, in this case can create a custom [Query](../04_Queries_&_Mutations/01_Queries.md).