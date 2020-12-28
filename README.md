# twig-code-coverage
This package is an implementation to trace code coverage of Twig templates based on [xdebug](https://xdebug.org/).

## Overwritten tags
In order to correctly collect all coverage, this library wraps the following tags that are supplied from Twig:
* `include`

If your implementation also provides implementations for any of these, you have to manually connect them to the wrap
provider - otherwise coverage reports will not be accurate. 