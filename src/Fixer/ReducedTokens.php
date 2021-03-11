<?php

/*
 * This file is part of the Arnapou Php72FixCount package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arnapou\Php72FixCount\Fixer;

use Traversable;

class ReducedTokens implements \IteratorAggregate
{
    const T_BRACE_OPEN    = 'brace open';
    const T_BRACE_CLOSE   = 'brace close';
    const T_CLASS         = 'class';
    const T_TRAIT         = 'trait';
    const T_FUNCTION      = 'function';
    const T_FUNCTION_CALL = 'function call';
    const T_NAMESPACE     = 'namespace';
    const T_USE_FUNCTION  = 'use function';

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
     * @var bool
     */
    private $forwardToFirstNamespace;

    /**
     * Parser constructor.
     * @param array $tokens
     * @param bool  $forwardToFirstNamespace
     */
    public function __construct(array $tokens, $forwardToFirstNamespace = true)
    {
        $this->tokens                  = $tokens;
        $this->count                   = \count($this->tokens);
        $this->forwardToFirstNamespace = $forwardToFirstNamespace;

        if (PHP_VERSION_ID < 80000) {
            if (!\defined('T_NAME_QUALIFIED')) {
                \define('T_NAME_QUALIFIED', 1e12 + 1);
            }
            if (!\defined('T_NAME_FULLY_QUALIFIED')) {
                \define('T_NAME_FULLY_QUALIFIED', 1e12 + 2);
            }
            if (!\defined('T_NAME_RELATIVE')) {
                \define('T_NAME_RELATIVE', 1e12 + 3);
            }
        }
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
        if ($this->forwardToFirstNamespace) {
            // this is a performance optimization when we need to parse only namespaced files
            $this->forwardToFirstNamespace();
        }
        $previous = null;
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
                        if ($this->index < $this->count && \is_array($this->tokens[$this->index]) && T_NS_SEPARATOR === $this->tokens[$this->index][0]) {
                            // skip class use like "namespace\XX\YY"
                            $this->fetchNextString();
                        } else {
                            $string = $this->fetchNextString();
                            yield [self::T_NAMESPACE, $string];
                            $this->index--;
                        }
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
                        if ($previous !== T_DOUBLE_COLON) {
                            $string = $this->fetchNextString();
                            yield [self::T_CLASS, $string];
                            $this->forwardToNextOpenBrace();
                            $this->index--;
                        }
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
                    case T_NAME_RELATIVE:
                    case T_NAME_FULLY_QUALIFIED:
                        if ($functionCall = $this->fetchFunctionCall()) {
                            yield [self::T_FUNCTION_CALL, $functionCall];
                        }
                        break;
                }
                $previous = \is_array($this->tokens[$this->index]) ? $this->tokens[$this->index][0] : null;
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
                case T_NAME_FULLY_QUALIFIED:
                case T_NAME_QUALIFIED:
                case T_NAME_RELATIVE:
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
        return rtrim($string, ':');
    }

    /**
     * @return void
     */
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
                    }

                    // use function <function>    => we calculate the alias
                    return [ltrim($string, '\\'), ltrim(substr($string, strrpos($string, '\\') ?: 0), '\\')];
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
