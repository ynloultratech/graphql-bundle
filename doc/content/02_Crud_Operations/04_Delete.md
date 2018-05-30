The delete operation is simply, 
the only that you need is add `MutationDelete` annotation.
 
 ````php
 /**
  * @ORM\Entity()
  * @ORM\Table()
  *
  * @GraphQL\ObjectType()
  * @GraphQL\QueryList()
  * @GraphQL\MutationAdd()
  * @GraphQL\MutationUpdate()
  * @GraphQL\MutationDelete()
  */
 class User implements NodeInterface
 {
 ````
