# twig-code-coverage
This package is an implementation to trace code coverage of Twig templates based on [xdebug](https://xdebug.org/).

## Usage
### Setup
The [`Bootstrapper`](src/Bootstrapper.php) class provides static methods to set up your Twig environment to be ready
for coverage testing. Once setup, you can render your templates the known way using the Twig `render()` method.

Ensure that you are not using a cache in your environment, otherwise the tracer will end up throwing a
_"Tracer could not find coverage statistics"_ exception.

### Accessing Results
To access render results, use the [`Tracer::getCoverages()`](src/Tracer.php) method. It returns an array of
[`TemplateCoverageResult`](src/TemplateCoverageResult.php) objects that provide the following methods:
* `getTemplateName(): string` Returns the rendered template name
* `getCalledLines(): int[]` Returns a list of line numbers that have been executed
* `getUncalledLines(): int[]` Returns a list of line numbers that have not been executed
* `getAllLinesByCallStatus() : array` Returns a map with the line numbers as keys and their call status as values
* `getCalledLinesPercentage(): string` Returns a string value between 0 and 100 with a precision of 4

**Be aware that**
* results of multiple runs are not aggregated; running `$twig->render('foo.twig')` twice will return two
coverage results
* all line numbers refer to the compiled PHP code, not the template source

#### Call Status
Call Status magic numbers are a 1:1 representation of the
[xdebug code coverage analysis](https://xdebug.org/docs/code_coverage#xdebug_get_code_coverage):
* `1` if a line has been executed
* `-1` if a line has not been executed
* `-2` if a line has no executable code in it

## Overwritten Tags
In order to correctly collect all coverage, this library wraps the following tags that are supplied from Twig:
* `include`



## Known Caveats
The following tags and functions are currently unsupported and will most likely produce unexpected results or even
fail when being rendered:
* `import`
* `from`
* `embed`
* `block`
* `parent()`

Additionally, if your implementation provides other extensions that render PHP template code outside of Twig's
`doDisplay()`, coverage analysis will most likely fail due to how we collect coverage.