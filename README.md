# Polygen-PHP

A PHP implementation of [Polygen](http://polygen.org), an OCaml tool to generate random sentences according to a
language definition (or grammar).

## Why?

I have used Polygen a few times and I like its idea, but I'd also like to use it on web pages, and you don't usually get
the chance to run arbitrary binaries on free or non-dedicated hosting services.

## Does it work?

Short answer: no.

Long answer: **not yet**.

## What works?

The Lexer is implemented, and ontop of that also an initial version of the parser has been written.
It parses Polygen grammar files in Concrete Syntax and has been built according to the Polygen documentation, especially
the [Concrete Section](https://polygen.org/it/manuale#4.1.1_Sintassi_concreta) and following.

There is an example grammar I have been using to test it in the tests folder, and an integration test that proves
that the parser reads it without blowing up and returns something that seems to have some sense.

The static checker is implemented, I know that the infinite loop check is for sure bugged, but at least it exists.
Also, the start symbol check does not allow to specify a custom start symbol yet.
And no warnings are recorded, only errors.
But hey, it's something!

The concrete to abstract syntax conversion has been implemented, and tests have been added for all 9 steps.

Finally, an interpreter has been written (but not tested yet). And it outputs more or less the expected strings, when
it does not crash. 

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
