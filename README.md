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
that the parser reads it without blowing up and returns something. Now, whether this is vaguely correct has yet to be
determined.

## The code is junk!

I have this feeling too, but I have never written an interpreter, and I don't think the Compiler courses I've taken at
the university have done a good job at teaching me how I should write one, so I'm improvising. If I get to the point
where this thing actually works, I might want to switch to something like the
[Doctrine Lexer](https://github.com/doctrine/lexer), but for now I prefer the handmade approach.

## Installation

There is no release yet. Clone the development branch and run `composer install`.

## Running tests

There are two test suites, **Unit** and **Integration** you can run both with `vendor/bin/phpunit`.
