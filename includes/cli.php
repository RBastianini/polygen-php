<?php

use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Stream\StreamInterface;
use Polygen\Grammar\Label;
use Polygen\Grammar\LabelSelection;
use Polygen\Language\Document;
use Polygen\Language\Interpretation\Context;
use Webmozart\Assert\Assert;

// Collection of a bunch of functions to be used in polygen.php bin file.

const OPT_LABEL = 'l';
const OPT_REQUIRED_VALUE = ':';
const OPT_DESTINATION = 'o';
const OPT_START_SYMBOL = 'S';
const OPT_ITERATE = 'X';
const OPT_INFO = 'info';
const OPT_SEED = 'seed';
const OPT_HELP = 'h';
const OPT_HELP_LONG = 'help';

const HELP_GRAMMAR = <<<'HELP'
I ::= "polygen-php help text.";

S ::= "polygen-php" "-" Catchphrase NewLine
        ^ Credits BlankLine
        ^ Usage BlankLine
        ^ Grammar BlankLine
        ^ Options BlankLine
        ^ OptionStart NewLine
        ^ OptionTimes NewLine
        ^ OptionSeed NewLine
        ^ OptionInfo NewLine
        ^ OptionLabel NewLine
        ;

BlankLine := NewLine ^ NewLine;
NewLine := "\n";
Adj := [silly|funny|famous];
Catchphrase ::= (
\a >>(
    ( Adj
        "PHP"
        (
            parser of \polygen grammar files
            | random sentence generator
        )
        | command line tool
    )
)<<
| _
) by \\riccardo \bastianini ^ ".";
Credits ::= "Based on the original Polygen" {implementation} and {documentation} by ("Alvise Spanò"|"Manta/Spinning Kids") ^ ".";
Usage ::= "usage: polygen.php [OPTIONS] [GRAMMAR]";
    
Grammar ::= "GRAMMAR: A" Adj "Polygen compatible grammar file.";

Options ::= "OPTIONS:";

OptionStart ::= "-S SYM    Use SYM as Non-terminal starting symbol (default: S).";
OptionTimes ::= "-X N      Iterate generation for N times (default: N = 1).";
OptionSeed ::= "--seed s  Pass arbitrary string 's' as random number generator seed.";
OptionInfo ::= "--info    Print information about the selected grammar (alias for -S I).";
OptionLabel ::= "-l LABEL Add LABEL to the initial select label set.";
HELP;

/**
 * Builds a context object using the options that were provided in the options array.
 *
 * @param string[] $options
 * @return Context
 */
function build_context(array $options)
{
    Assert::integerish($options[OPT_ITERATE], sprintf('-%s option expects an integer.', OPT_ITERATE));
    Assert::greaterThan($options[OPT_ITERATE], 0, sprintf('-%s option expects a value greater than zero.', OPT_ITERATE));
    return Context::get(
        $options[OPT_INFO]
            ? Document::INFORMATION
            : $options[OPT_START_SYMBOL],
        $options[OPT_SEED],
        $options[OPT_LABEL]
            ? LabelSelection::forLabel(new Label($options[OPT_LABEL]))
            : null
    );
}

/**
 * Validates the provided path and returns an error if the file can't be read.
 *
 * @param string $path
 */
function validate_file_path($path)
{
    if (!file_exists($path)) {
        throw new \RuntimeException("$path does not exist.");
    }
    if (!is_readable($path)) {
        throw new \RuntimeException("$path is not readable.");
    }
    if (!is_file($path)) {
        throw new \RuntimeException("$path is not a file.");
    }
}

/**
 * Opens the file at the specified path and returns a stream.
 *
 * @param string $path
 * @return StreamInterface
 */
function load_grammar($path)
{
    $grammar = @fopen($path, 'r');
    if ($grammar === false) {
        throw new \RuntimeException(error_get_last());
    }
    return Stream::factory($grammar);
}
