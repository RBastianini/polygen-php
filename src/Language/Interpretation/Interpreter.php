<?php

namespace Polygen\Language\Interpretation;

use Polygen\Document;
use Polygen\Language\Token\Token;
use Polygen\Language\Token\Type;
use Webmozart\Assert\Assert;

/**
 * Polygen Document interpreter.
 */
class Interpreter
{
    /**
     * Interprets the document, generates a sentence and returns it.
     * @return string
     */
    public function interpret(Document $document, Context $context)
    {
        $generator = new TokenSequenceGenerator();
        $generatedSequence = $generator->generateSequence($document, $context);
        return $this->concatenate($generatedSequence);
    }

    /**
     * Take a string of tokens and interpret them to generate a string.
     *
     * @param Token[] $tokens
     * @return string
     */
    private function concatenate(array $tokens)
    {
        // We'll build here an array of strings and we will implode() them together before returning, using empty
        // strings as glue. To make this work, every time the concatenation toggle is OFF we add a space followed by the
        // string we just read to the strings array. When the concatenation toggle is ON, we only add the string we just
        // read.
        $strings = [];
        // We need to consider the concatenation toggle "^" to be ON for the first round, in order for the implode()
        // trick to work.
        $concatenationToggle = true;
        $capslockToggle = false;
        foreach ($tokens as $token) {
            Assert::isInstanceOf($token, $token);
            if ($token->getType() === Type::terminatingSymbol()) {
                $text = $token->getValue();
                if (!$concatenationToggle) {
                    $strings[] = ' ';
                }
                if ($capslockToggle) {
                    $text = ucfirst($text);
                }
                $concatenationToggle = false;
                $capslockToggle = false;
                $strings[] = $text;
            } else {
                switch ($token->getType()) {
                    case Type::backslash():
                        $capslockToggle = true;
                        break;
                    case Type::cap():
                        $concatenationToggle = true;
                        break;
                    case Type::underscore():
                        break;
                    default:
                        throw new \RuntimeException(
                            "Unexpected token of type {$token->getType()} ({$token->getValue()}) found while concatenating."
                        );
                }
            }
        }
        return implode($strings);
    }
}
