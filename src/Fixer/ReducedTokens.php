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
     * @return \Generator
     */
    private function reduce()
    {
        $this->index = 0;
        while ($this->index < $this->count) {
            $token = $this->token();

            if ($token[0] === T_WHITESPACE) {
                // skip whitespaces
            } elseif ($token === [null, '{', null] || $token[0] === T_CURLY_OPEN || $token[0] === T_STRING_VARNAME) {
                yield self::T_BRACE_OPEN => '{';
            } elseif ($token === [null, '}', null]) {
                yield self::T_BRACE_CLOSE => '}';
            } elseif ($token[0] === T_NAMESPACE) {
                yield self::T_NAMESPACE => $this->getFollowingString(1, -1);
            } elseif ($token[0] === T_USE) {
                $this->ignoreFollowingWhitespaces(1);
                if ($this->token()[0] === T_FUNCTION) {
                    $string = $this->getFollowingString(1);
                    if ($this->token()[0] === T_AS) {
                        yield self::T_USE_FUNCTION => [ltrim($string, '\\'), $this->getFollowingString(1)];
                    } else {
                        yield self::T_USE_FUNCTION => [ltrim($string, '\\'), ltrim(substr($string, strrpos($string, '\\') ?: 0), '\\')];
                    }
                }
            } elseif ($token[0] === T_OBJECT_OPERATOR) {
                $this->getFollowingString(1, -1); // skip method call
            } elseif ($token[0] === T_NEW) {
                $this->getFollowingString(1, -1); // skip class instanciation
            } elseif ($token[0] === T_FUNCTION) {
                $string = $this->getFollowingString(1);
                if ($this->token() === [null, '(', null]) {
                    yield self::T_FUNCTION => $string;
                }
            } elseif ($token[0] === T_CLASS) {
                yield self::T_CLASS => $this->getFollowingString(1);
                $this->forwardToNextOpenBrace(0, -1);
            } elseif ($token[0] === T_TRAIT) {
                yield self::T_TRAIT => $this->getFollowingString(1);
                $this->forwardToNextOpenBrace(0, -1);
            } elseif ($token[0] === T_INTERFACE) {
                $this->forwardToNextOpenBrace();
                $this->skipBraceBlock();
            } elseif ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR) {
                $string = $this->getFollowingString();
                if ($this->token() === [null, '(', null]) {
                    yield self::T_FUNCTION_CALL => $string;
                }
            }
            $this->index++;
        }
    }

    /**
     * normalize tokens
     * @return array
     */
    private function token()
    {
        if (!isset($this->tokens[$this->index])) {
            return [null, null, null];
        }
        $token = $this->tokens[$this->index];
        if (\is_array($token)) {
            return $token;
        } elseif (\is_string($token)) {
            return [null, $token, null];
        } else {
            return [null, null, null];
        }
    }

    /**
     * @param int $jumpBefore
     * @param int $jumpAfter
     * @return string
     */
    private function getFollowingString($jumpBefore = 0, $jumpAfter = 0)
    {
        $this->index += $jumpBefore;

        $string = '';
        while ($this->index < $this->count) {
            $token = $this->token();
            if ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR || $token[0] === T_DOUBLE_COLON) {
                $string .= $token[1];
                $this->index++;
            } elseif ($token[0] === T_WHITESPACE) {
                $this->index++;
            } else {
                break;
            }
        }

        $this->index += $jumpAfter;
        return $string;
    }

    /**
     * @param int $jumpBefore
     */
    private function ignoreFollowingWhitespaces($jumpBefore = 0)
    {
        $this->index += $jumpBefore;

        while ($this->token()[0] === T_WHITESPACE) {
            $this->index++;
        }
    }

    /**
     * skip all and stop on first opened brace found
     * @param int $jumpBefore
     * @param int $jumpAfter
     */
    private function forwardToNextOpenBrace($jumpBefore = 0, $jumpAfter = 0)
    {
        $this->index += $jumpBefore;
        while ($this->token() !== [null, '{', null]) {
            $this->index++;
        }
        $this->index += $jumpAfter;
    }

    /**
     * if the current token is not an opened brace, the function won't do anything
     */
    private function skipBraceBlock()
    {
        $braces = 0;
        while ($this->index < $this->count) {
            $token = $this->token();

            if ($token === [null, '{', null]) {
                $braces++;
            } elseif ($token === [null, '}', null]) {
                $braces--;
            }

            $this->index++;
            if ($braces == 0) {
                break;
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
}
