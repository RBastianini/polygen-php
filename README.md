# Polygen-PHP

A PHP implementation of [Polygen](http://polygen.org), an OCaml tool to generate random sentences according to a language definition (or grammar).

## Why?

I have used Polygen a few times and I like its idea, but I'd also like to use it on web pages, and you don't usually get the chance to run arbitrary binaries on free or non-dedicated hosting services.

## Does it work?

Short answer: no.

Long answer: **not yet**.

## What works?

Currently just the Lexer is implemented. It takes a Stream object (I think a CachingStream is better) representing a string and returns a stream of Token objects. From there, it's still a long way until we have a working Polygen alternative in PHP.

```php
<?php
  // Parse a string into a stream of tokens
  $sourceStream = new \GuzzleHttp\Stream\CachingStream(
    \GuzzleHttp\Stream\Stream::factory(
      '(*This is a comment in Polygen syntax*)'
    )
  );
  $lexer = new \Polygen\Language\Lexing\Lexer($source);
  foreach ($lexer->getTokens() as $token) {
    print_r("$token\n");
    // Prints <COMMENT, This is a comment in Polygen syntax>
    // And actually another <WHITESPACE, > token that is not there, and I don't know yet where it comes from.
  }
```

## The code is junk!

I have this feeling too, but I have never written an interpreter, and I don't think the Compiler courses I've taken at the university have done a good job at teaching me how I should write one, so I'm improvising. If I get to the point where this thing actually works, I might want to switch to something like the [Doctrine Lexer](https://github.com/doctrine/lexer), but for now I prefer the handmade approach.

## Installation

There is no release yet. Clone the development branch and run `composer install`.

## Running tests

There are two test suites, **Unit** and **Integration** you can run both with `vendor/bin/phpunit`.
