<?php

namespace Arnapou\Php72FixCount\Fixer;

class Parser
{
    const T_BRACE_OPEN = 'brace open';
    const T_BRACE_CLOSE = 'brace close';
    const T_CLASS = 'class';
    const T_FUNCTION = 'function';
    const T_FUNCTION_CALL = 'function call';
    const T_NAMESPACE = 'namespace';
    const T_USE_FUNCTION = 'use function';

    /**
     * @var array
     */
    private $foundConflicts = [];
    /**
     * @var array
     */
    private $foundFixable = [];
    /**
     * @var array
     */
    private $foundUnfixable = [];
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
     * @var string
     */
    private $namespace;

    /**
     * Parser constructor.
     * @param $filename
     */
    public function __construct($filename)
    {
        $this->tokens = token_get_all(file_get_contents($filename));
        $this->count  = \count($this->tokens);
        $this->parse();
    }

    private function parse()
    {
        $braces        = 0;
        $namespace     = '';
        $class         = '';
        $classBrace    = 0;
        $function      = '';
        $functionBrace = 0;
        foreach ($this->reducedTokens() as $type => $string) {
            if (self::T_BRACE_OPEN === $type) {
                $braces++;
            } elseif (self::T_BRACE_CLOSE === $type) {
                $braces--;
                if ($class && $classBrace === $braces) {
                    $class = '';
                }
                if ($function && $functionBrace === $braces) {
                    $function = '';
                }
            } elseif (self::T_NAMESPACE === $type) {
                $namespace = $string;
            } elseif ($namespace) {
                if (self::T_USE_FUNCTION === $type) {
                    if (strtolower($string) === 'count' || substr(strtolower($string), -6) === '\count') {
                        $this->foundConflicts[$namespace] = isset($this->foundConflicts[$namespace]) ? $this->foundConflicts[$namespace] + 1 : 1;
                    }
                } elseif (self::T_CLASS === $type) {
                    $class      = $string;
                    $classBrace = $braces;
                } elseif (self::T_FUNCTION === $type) {
                    $function      = $string;
                    $functionBrace = $braces;

                    if (!$class && strtolower($string) === 'count') {
                        $this->foundConflicts[$namespace] = isset($this->foundConflicts[$namespace]) ? $this->foundConflicts[$namespace] + 1 : 1;
                    }
                } elseif (self::T_FUNCTION_CALL === $type) {
                    if (strtolower($string) === 'count') {
                        $this->foundFixable[$namespace] = isset($this->foundFixable[$namespace]) ? $this->foundFixable[$namespace] + 1 : 1;
                    } elseif (strtolower($string) === '\count') {
                        $this->foundUnfixable[$namespace] = isset($this->foundUnfixable[$namespace]) ? $this->foundUnfixable[$namespace] + 1 : 1;
                    }
                }
            }
        }
    }

    /**
     * @return \Generator
     */
    private function reducedTokens()
    {
        $this->index = 0;
        while ($this->index < $this->count) {
            $token = $this->token();

            if ($token[0] === T_WHITESPACE) {
                // skip whitespaces
            } elseif ($token[0] === null && $token[1] === '{') {
                yield self::T_BRACE_OPEN => '{';
            } elseif ($token[0] === null && $token[1] === '}') {
                yield self::T_BRACE_CLOSE => '}';
            } elseif ($token[0] === T_NAMESPACE) {
                yield self::T_NAMESPACE => $this->getFollowingString(1, -1);
            } elseif ($token[0] === T_USE) {
                $this->ignoreFollowingWhitespaces(1);
                if ($this->token()[0] === T_FUNCTION) {
                    $string = $this->getFollowingString(1);
                    if ($this->token()[0] === T_AS) {
                        $string = $this->getFollowingString(1);
                    }
                    yield self::T_USE_FUNCTION => $string;
                }
            } elseif ($token[0] === T_FUNCTION) {
                $string = $this->getFollowingString(1);
                if ($this->token() === [null, '(', null]) {
                    yield self::T_FUNCTION => $string;
                }
            } elseif ($token[0] === T_CLASS) {
                yield self::T_CLASS => $this->getFollowingString(1, -1);
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
     * @return array
     */
    public function getConflicts()
    {
        return $this->foundConflicts;
    }

    /**
     * @return array
     */
    public function getFixable()
    {
        return $this->foundFixable;
    }

    /**
     * @return array
     */
    public function getUnfixable()
    {
        return $this->foundUnfixable;
    }
}
