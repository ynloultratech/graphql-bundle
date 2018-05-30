# Contributing to GraphQL Bundle

## Workflow

If your contribution requires significant or breaking changes, or if you plan to propose a major new feature,
we recommend you to create an issue on the [GitHub](https://github.com/ynloultratech/graphql-bundle/issues) with
a brief proposal and discuss it with us first.

For smaller contributions just use this workflow:

* Fork the project.
* Add your features and or bug fixes.
* Add tests.
* Send a pull request

## Using GraphQL Bundle from a Git checkout

```
$ git clone https://github.com/ynloultratech/graphql-bundle.git
$ cd graphql-bundle
$ composer install
```

## Running tests

From the project root:

PHP Unit Tests:

```
$ ./bin/phpunit
```

Behat Tests:

```
$ ./bin/behat
```