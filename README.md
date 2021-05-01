# Polygen-PHP

[Polygen](https://github.com/alvisespano/Polygen) is a tool that parses text files containing a grammar definition and
generates text according to that definition. Polygen-PHP does the same, but in PHP 5.6+ and is compatible with the same
grammar files, so now you can use Polygen on your website even if it's on a shared hosting service.

For example, the following grammar file
```
S ::= this is Subject and is Quantity Adjective;
Subject ::= a grammar | polygen;
Quantity ::= rather | quite | very;
Adjective ::= interesting | remarkable | dumb;
```
might result in the following sentences being generated:
* this is a grammar and is rather dumb
* this is polygen and is quite remarkable
* this is a grammar and is very interesting

... and so on.

There are many more features in the language, check out the
[official Polygen documentation](http://htmlpreview.github.io/?https://github.com/alvisespano/Polygen/blob/dca27bd02613613d60a0e024c1668e8459de7288/docs/polygen-spec_EN.html)
to learn everything about it.

## Installation

You can install Polygen-PHP using composer: `composer require rb/polygen-php`

## Usage instructions

```php
<?php
$polygen = new \Polygen\Polygen();
$your_grammar = \GuzzleHttp\Stream\Stream::factory('S ::= hello world;');
$document = $polygen->getDocument($your_grammar);
var_dump($polygen->generate($document)); // Will print "hello world".
```

### Parse once, produce many (POPM)
It's not necessary to parse the source grammar every time a production is wanted: once a Polygen document is obtained, it
can be `serialize()`d to a string and stored as a string constant. This way, the parsing can be done only once at
"compile time", then at runtime, it's sufficient to `unserialize()` the string back to a Polygen document, and it can be
used as usual to obtain a production.

### Command line usage
There is also a CLI tool that you can use. It's not refined at all, since it is intended for debugging only, but the
basics work.
If you require this package as a dependency of your project, Composer must have placed the CLI tool in your vendor/bin
folder, so you can run it with `./vendor/bin/polygen.php`.
A small usage will be printed if launched with no parameters (or with `-h` or `--help` parameters).

## Known issues

### Static checking

* I believe that the static infinite recursion check is broken, but I have not proved this yet. Just try to avoid
grammars with circular references between declarations and everything will be fine.
* Only errors are reported by the static check, no warnings have been implemented.

## Running tests

There are two test suites, **Unit** and **Integration** you can run both with `vendor/bin/phpunit`.
