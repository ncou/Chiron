
Coding Standard
***************

*This document describes rules and recommendations for developing Nette. When contributing code to Nette, you must follow them. The easiest way how to do it is to imitate the existing code.
The idea is to make all the code look like it was written by one person.*

Nette fully follows [PSR-12 Extended Coding Style](https://www.php-fig.org/psr/psr-12/) with one exception: it historically uses tabs instead of spaces for indentation.


General rules
=============

- Every PHP file must contain `declare(strict_types=1)`
- Two empty lines are used to seperate methods for better readability.
- The reason for using shut-up operator must be documented: `@mkdir($dir); // @ - directory may exist`
- If weak typed comparison operator is used (ie. `==`, `!=`, ...), the intention must be documented: `// == to accept null`
- You can write more exceptions into one file `exceptions.php`
- Interfaces do not specify method visibility because they are always public.
- Every properties, methods and parameters must have documented type. Either natively or via annotation.
- Arrays must be written by short notation.
- The single quote should be used to demarcate the string, except when a literal itself contains apostrophes.


Naming conventions
==================

- Avoid using abbreviations unless the full name is excessive.
- Use uppercase for two-letter abbreviations, and pascal/camel case for longer abbreviations.
- Use a noun or noun phrase for class name.
- Class names must contain not only specificity (`Array`) but also generality (`ArrayIterator`). The exception are PHP attributes.
- Interfaces and abstract classes should not contain prefixes or postfixes like `Abstract`, `Interface` or `I`.


Documentation Blocks (phpDoc)
=============================

The main rule: never duplicate any signature information like parameter type or return type with no added value.

Documentation block for class definition:

- Starts by a class description.
- Empty line follows.
- The `@property` (or `@property-read`, `@property-write`) annotations follow, one by line. Syntax is: annotation, space, type, space, $name.
- The `@method` annotations follow, one by line. Syntax is: annotation, space, return type, space, name(type $param, ...).
- The `@author` annotation is omitted. The authorship is kept in a source code history.
- The `@internal` or `@deprecated` annotations can be used.

```php
/**
 * MIME message part.
 *
 * @property string $encoding
 * @property-read array $headers
 * @method string getSomething(string $name)
 * @method static bool isEnabled()
 */
```

Documentation block for property that contains only `@var` annotation should be in single line:

```php
/** @var string */
private $name;
```

Documentation block for method definition:

- Starts by a short method description.
- No empty line.
- The `@param` annotations, one by line.
- The `@return` annotation.
- The `@throws` annotations, one by line.
- The `@internal` or `@deprecated` annotations can be used.

Every annotation is followed by one space, except for the `@param` which is followed by two spaces for better readability.

```php
/**
 * Finds a file in directory.
 * @param  array|object  $options
 * @return static
 * @throws DirectoryNotFoundException
 */
public function find(string $dir, $options): self
```
