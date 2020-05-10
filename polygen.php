#!/usr/bin/env php
<?php

// ⣀⡀ ⢀⡀ ⡇ ⡀⢀ ⢀⡀ ⢀⡀ ⣀⡀
// ⡧⠜ ⠣⠜ ⠣ ⣑⡺ ⣑⡺ ⠣⠭ ⠇⠸

use GuzzleHttp\Stream\Stream;
use Polygen\Language\Document;
use Polygen\Language\Exceptions\StaticCheckException;
use Polygen\Polygen;

if (file_exists($autoloadFile = __DIR__ . '/vendor/autoload.php')
    || file_exists($autoloadFile = __DIR__ . '/../autoload.php')
    || file_exists($autoloadFile = __DIR__ . '/../../autoload.php')
) {
    include_once($autoloadFile);
} else {
    throw new \Exception("Could not locate autoload.php. __DIR__ is " . __DIR__);
}

require_once(__DIR__ . '/includes/cli.php');

// Execution starts here

$options = getopt(
    implode([
        OPT_LABEL, OPT_REQUIRED_VALUE,
        OPT_DESTINATION, OPT_REQUIRED_VALUE,
        OPT_START_SYMBOL, OPT_REQUIRED_VALUE,
        OPT_ITERATE, OPT_REQUIRED_VALUE
    ]),
    [
        OPT_SEED . OPT_REQUIRED_VALUE,
    ]
);

$path = $argv[$argc - 1];

$polygen = new Polygen();

if ($argc === 1) {
    echo $polygen->generate($polygen->getDocument(Stream::factory(HELP_GRAMMAR), 'S'));
    exit(0);
}

$options += [
    OPT_START_SYMBOL => Document::START,
    OPT_SEED => null,
    OPT_ITERATE => 1,
];

$context = build_context($options);

validate_file_path($path);

try {
    $document = $polygen->getDocument(load_grammar($path), $options[OPT_START_SYMBOL]);
} catch (StaticCheckException $e) {
    echo "Static Check error(s) found:\n";
    $i = 0;
    foreach ($e->getErrors()->getErrors() as $error) {
        $i++;
        echo "{$i}) {$error->getMessage()}\n";
    }
}
for ($i = 0; $i < $options[OPT_ITERATE]; $i++) {
    echo $polygen->generate($document, $context);
    echo PHP_EOL;
}
