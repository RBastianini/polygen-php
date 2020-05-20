# Polygen-PHP

A PHP implementation of [Polygen](http://polygen.org), an OCaml tool to generate random sentences according to a
language definition (or grammar).

## What?!
Polygen is a tool that parses text files containing a grammar definition and generates text according to that
definition.

For example, given the following grammar file
```
S ::= \this is Subject and is Quantity Adjective;
Subject ::= a grammar | \polygen;
Quantity ::= rather | quite | very;
Adjective ::= interesting | remarkable | dumb;
```
might result in the following sentences being generated:
* This is a grammar and is rather dumb
* This is Polygen and is quite remarkable
* This is a grammar and is very interesting

... and so on.

There are many more features in the language, but unfortunately the English documentation is no longer available on the
official Polygen website, but hopefully automatic transations and the examples in the
[Italian documentation](https://polygen.org/it/manuale) will be enough to get the hang of it.

## Why?

I have used Polygen a few times and I like its idea, but I'd also like to use it to generate web pages, and you don't
usually get the chance to run arbitrary binaries on free or non-dedicated hosting services. Finally, I've always been
fascinated by compilers, interpreters and parsers, I have long admired people able to write one, but have never tried
doing one myself.

## Does it work?

Short answer: for the most part.

### What works?

* The Lexer is implemented, and ontop of that also an initial version of the parser has been written.
It parses Polygen grammar files in Concrete Syntax and has been built according to the Polygen documentation, especially
the [Concrete Section](https://polygen.org/it/manuale#4.1.1_Sintassi_concreta) and following.
* There are some static checks in place.
* The concrete to abstract syntax conversion has been implemented, and tests have been added for all 9 steps.
* An interpreter has been written and tested (but not completely) and it appears to output more or less the expected
strings.
* A CLI tool.

### What does not work?
#### Multiple label selection groups
Grammars containing multiple label selection groups like
```
S ::= Something.(A|B).(C|D);
```
will result in a parsing error. The reason is that, despite the syntax being supported by the original Polygen, it is
not represented in the [Concrete Syntax](https://polygen.org/it/manuale#4.1.1_Sintassi_concreta) section of the
documentation that I have tried to implement as accurately as possible.
#### Static checking
* I believe that the static infinite recursion check is broken, but I have not proved this yet.
* Only errors are reported by the static check, no warnings have been implemented.
#### Everything else I don't know it doesn't work
🤷
## The code is junk!

I have this feeling too, but I have never written an interpreter, and I don't think the Compiler courses I've taken at
the university have done a good job at teaching me how I should write one, so I'm improvising.

## Installation

There is no release yet. Clone the development branch and run `composer install`.

## Running tests

There are two test suites, **Unit** and **Integration** you can run both with `vendor/bin/phpunit`.

## How can I use it on a grammar I have written?

```php
<?php
$polygen = new \Polygen\Polygen();
$document = $polygen->getDocument($source);
var_dump($polygen->generate($document));
```

### Command line usage
There is also a CLI tool that you can use. It's not refined at all, but the basics work.
If you require this package as a dependency of your project (which you should not do yet), Composer must have placed the
CLI tool in your vendor/bin folder, so you can run it with `./vendor/bin/polygen.php`.
If you checked out this repository, you can run the CLI tool by just typing `./polygen.php` instead.
A small usage will be printed if launched with no parameters (or with `-h` or `--help` parameters).
