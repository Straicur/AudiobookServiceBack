# How to Contribute

## Pull Requests

1. Fork the repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch to the **develop** branch

It is very important to separate new features or improvements into separate feature branches, and to send a
pull request for each branch. This allows me to review and pull in new features or improvements individually.

## Style Guide

All pull requests must adhere to the [PSR-12 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-12-extended-coding-style-guide.md).

## Integration Testing

All pull requests must be accompanied by passing integration tests and complete code coverage. Tests are required for each endpoint, generator and command.

To run all the tests and coding standards checks, execute the following from the
command line, while in the project root directory:

```
make tests
```
