By default all exceptions are caught, collected and putting into queue automatically, but sometimes
we need to do this task manually. For example, during the process of a mutation if your mutation
throw a exception the exceptions is collected and displayed but the data will be null.

Example of throwing a exception when the error happen in the resolver:
````php
if (!$this->isValidProduct($transaction->getProduct())) {
    $transaction->setStatus(TransactionStatusType::FAILED);
    $entityManager->flush();
    throw new InvalidProductException();
}

//...

$transaction->setStatus(TransactionStatusType::COMPLETED);
$entityManager->flush();

return $transaction;
````

The above example is a common use, as you can see a exception is throw if the product is not valid and 
the response display the error without the transaction.

````json
{
  "errors": [
    {
      "code": 2101,
      "tracking_id": "2353CA4C-7582-C83A-3194-FE3D34A4",
      "message": "Invalid Product",
      "category": "user"
    }
  ],
  "data": {
    "submitTransaction": null
  }
}
````

That is OK for that scenario, but in some situations you need include data and errors in the response.

````json
{
  "errors": [
    {
      "code": 2103,
      "tracking_id": "2353CA4C-7582-C83A-3194-FE3D34A4",
      "message": "This transaction can take a while to process, be patient.",
      "category": "user"
    }
  ],
  "data": {
    "submitTransaction": {
      "id": "VHJhbnNhY3Rpb246MQ==",
      "number": "00831043",
      "status": "PENDING"
    }
  }
}
````

In these cases you must use the `ErrorQueue` and continue your workflow as normally and return the data
if is the case.

````
use Ynlo\GraphQLBundle\Error\ErrorQueue;

//...

if ($this->isToProcessLater($transaction)) {
    $transaction->setStatus(TransactionStatusType::PENDING);
    $entityManager->flush();
    ErrorQueue::throw(new PendingTransactionException());
    return $transaction;
}

//...

$transaction->setStatus(TransactionStatusType::COMPLETED);
$entityManager->flush();

return $transaction;
````

After the query execution all queued errors will be displayed aside the data in the response.
 

