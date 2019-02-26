<?php

namespace Arnapou\Php72FixCount\Fixer;

use Traversable;

class ReducedTokens implements \IteratorAggregate
{
    const T_BRACE_OPEN = 'brace open';
    const T_BRACE_CLOSE = 'brace close';
    const T_CLASS = 'class';
    const T_TRAIT = 'trait';
    const T_FUNCTION = 'function';
    const T_FUNCTION_CALL = 'function call';
    const T_NAMESPACE = 'namespace';
    const T_USE_FUNCTION = 'use function';

    /**
     * @var int
     */
    private $index = 0;
    /**
     * @var int
     */
    private $count;
    /**
     * @var array
     */
    private $tokens;

    /**
     * Parser constructor.
     * @param array $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
        $this->count  = \count($this->tokens);
    }

    /**
     * @return Traversable
     */
    public function getIterator()
    {
        return $this->reduce();
    }

    /**
     * @return \Generator
     */
    private function reduce()
    {
        $this->index = 0;
        $this->forwardToFirstNamespace(); // we ignore files without namespace (perf matters)
        while ($this->index < $this->count) {
            $token = $this->tokens[$this->index];

            if (\is_string($token)) {
                $token = [$token];
            }
            if (\is_array($token)) {
                switch ($token[0]) {
                    case '{':
                    case T_CURLY_OPEN:
                    case T_STRING_VARNAME:
                        yield [self::T_BRACE_OPEN, '{'];
                        break;
                    case '}':
                        yield [self::T_BRACE_CLOSE, '}'];
                        break;
                    case T_NAMESPACE:
                        $this->index++;
                        $string = $this->fetchNextString();
                        yield [self::T_NAMESPACE, $string];
                        $this->index--;
                        break;
                    case T_USE:
                        if ($useFunction = $this->fetchUseFunction()) {
                            yield [self::T_USE_FUNCTION, $useFunction];
                        }
                        break;
                    case T_OBJECT_OPERATOR: // skip method call          ->xxx()
                    case T_NEW:             // skip class instanciation  new xxx()
                        $this->index++;
                        $this->fetchNextString();
                        $this->index--;
                        break;
                    case T_FUNCTION:
                        if ($function = $this->fetchFunction()) {
                            yield [self::T_FUNCTION, $function];
                        }
                        break;
                    case T_CLASS:
                        $this->index++;
                        $string = $this->fetchNextString();
                        yield [self::T_CLASS, $string];
                        $this->forwardToNextOpenBrace();
                        $this->index--;
                        break;
                    case T_TRAIT:
                        $this->index++;
                        $string = $this->fetchNextString();
                        yield [self::T_TRAIT, $string];
                        $this->forwardToNextOpenBrace();
                        $this->index--;
                        break;
                    case T_INTERFACE:
                        $this->skipNextBraceBlock();
                        break;
                    case T_STRING:
                    case T_NS_SEPARATOR:
                        if ($functionCall = $this->fetchFunctionCall()) {
                            yield [self::T_FUNCTION_CALL, $functionCall];
                        }
                        break;
                }
            }

            $this->index++;
        }
    }

    /**
     * @return string
     */
    private function fetchNextString()
    {
        $string = '';
        while ($this->index < $this->count) {
            $token = $this->tokens[$this->index];
            if (!\is_array($token)) {
                break;
            }
            switch ($token[0]) {
                case T_STRING:
                case T_NS_SEPARATOR:
                case T_DOUBLE_COLON:
                    $string .= $token[1];
                    $this->index++;
                    break;
                case T_WHITESPACE:
                    $this->index++;
                    break;
                default:
                    break 2;
            }
        }
        return $string;
    }

    private function skipWhitespaces()
    {
        while ($this->index < $this->count) {
            $token = $this->tokens[$this->index];
            if (!\is_array($token) || $token[0] !== T_WHITESPACE) {
                break;
            }
            $this->index++;
        }
    }

    /**
     * skip all tokens and stop on first namespace
     */
    private function forwardToFirstNamespace()
    {
        while ($this->index < $this->count) {
            $token = $this->tokens[$this->index];
            if (\is_array($token) && $token[0] === T_NAMESPACE) {
                break;
            }
            $this->index++;
        }
    }

    /**
     * skip all tokens and stop on first opened brace found
     */
    private function forwardToNextOpenBrace()
    {
        while ($this->index < $this->count) {
            $token = $this->tokens[$this->index];
            if ($token === '{' || \is_array($token) && ($token[0] === T_CURLY_OPEN || $token[0] === T_STRING_VARNAME)) {
                break;
            }
            $this->index++;
        }
    }

    /**
     * Completely skip the next brace block
     */
    private function skipNextBraceBlock()
    {
        $this->forwardToNextOpenBrace();
        $braces = 1;
        $this->index++;

        while ($this->index < $this->count) {
            $token = $this->tokens[$this->index];

            if ($token === '{' || \is_array($token) && ($token[0] === T_CURLY_OPEN || $token[0] === T_STRING_VARNAME)) {
                $braces++;
            } elseif ($token === '}') {
                $braces--;
            }

            $this->index++;
            if ($braces == 0) {
                break;
            }
        }
    }

    /**
     * @return array|null
     */
    private function fetchUseFunction()
    {
        $this->index++; // jump over the T_USE
        $this->skipWhitespaces();
        if ($this->index < $this->count) {
            $token = $this->tokens[$this->index];
            if (\is_array($token) && $token[0] === T_FUNCTION) {
                $this->index++; // jump over the T_FUNCTION
                $string = $this->fetchNextString();
                if ($string && $this->index < $this->count) {
                    $token = $this->tokens[$this->index];
                    if (\is_array($token) && $token[0] === T_AS) {
                        // use function <function> as <alias>
                        $this->index++;
                        return [ltrim($string, '\\'), $this->fetchNextString()];
                    } else {
                        // use function <function>    => we calculate the alias
                        return [ltrim($string, '\\'), ltrim(substr($string, strrpos($string, '\\') ?: 0), '\\')];
                    }
                }
            }
        }
        return null;
    }

    /**
     * @return string|null
     */
    private function fetchFunction()
    {
        $this->index++; // jump over the T_FUNCTION
        $string = $this->fetchNextString();
        if ($string && $this->index < $this->count) {
            $token = $this->tokens[$this->index];
            if ($token === '(') {
                return $string;
            }
        }
        return null;
    }

    /**
     * @return string|null
     */
    private function fetchFunctionCall()
    {
        $string = $this->fetchNextString();
        if ($string && $this->index < $this->count) {
            $token = $this->tokens[$this->index];
            if ($token === '(') {
                return $string;
            }
        }
        return null;
    }
}
