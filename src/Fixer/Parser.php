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
    private $target;

    /**
     * Parser constructor.
     * @param string $filename
     * @param string $target 'count' or 'sizeof'
     */
    public function __construct($filename, $target = 'count')
    {
        if (!\in_array($target, ['count', 'sizeof'])) {
            throw new \InvalidArgumentException("Target argument is not valid, it should be 'count' or 'sizeof'.");
        }
        $this->target = $target;
        $this->tokens = token_get_all(file_get_contents($filename));
        $this->count  = \count($this->tokens);
        $this->parse();
    }

    private function parse()
    {
        $braces            = 0;
        $namespace         = '';
        $class             = '';
        $classBrace        = -1;
        $function          = '';
        $functionBrace     = -1;
        $useFunctionNative = false;
        foreach ($this->reducedTokens() as $type => $value) {
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
                $namespace         = $value;
                $useFunctionNative = false;
            } elseif ($namespace) {
                if (self::T_USE_FUNCTION === $type) {
                    if (strtolower($value[1]) === $this->target) {
                        if (strtolower($value[0]) === $this->target) {
                            $useFunctionNative = true;
                            $this->addUnfixable($namespace);
                        } else {
                            $this->addConflict($namespace);
                        }
                    }
                } elseif (self::T_CLASS === $type) {
                    $class      = $value;
                    $classBrace = $braces;
                } elseif (self::T_FUNCTION === $type) {
                    $function      = $value;
                    $functionBrace = $braces;
                    if (!$class && strtolower($value) === $this->target) {
                        $this->addConflict($namespace);
                    }
                } elseif (self::T_FUNCTION_CALL === $type) {
                    if (strtolower($value) === $this->target) {
                        if ($useFunctionNative) {
                            $this->addUnfixable($namespace);
                        } else {
                            $this->addFixable($namespace);
                        }
                    } elseif (strtolower($value) === '\\' . $this->target) {
                        $this->addUnfixable($namespace);
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
            } elseif ($token === [null, '{', null]) {
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
                yield self::T_CLASS => $this->getFollowingString(1, -1);
                $this->forwardTo([null, '{', null], 1, -1);
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
     * @param array $token
     * @param int   $jumpBefore
     * @param int   $jumpAfter
     */
    private function forwardTo($token, $jumpBefore = 0, $jumpAfter = 0)
    {
        $this->index += $jumpBefore;
        while ($this->token() !== $token) {
            $this->index++;
        }
        $this->index += $jumpAfter;
    }

    /**
     * @param string $namespace
     */
    private function addConflict($namespace)
    {
        $this->foundConflicts[$namespace] = isset($this->foundConflicts[$namespace]) ? $this->foundConflicts[$namespace] + 1 : 1;
    }

    /**
     * @param string $namespace
     */
    private function addFixable($namespace)
    {
        $this->foundFixable[$namespace] = isset($this->foundFixable[$namespace]) ? $this->foundFixable[$namespace] + 1 : 1;
    }

    /**
     * @param string $namespace
     */
    private function addUnfixable($namespace)
    {
        $this->foundUnfixable[$namespace] = isset($this->foundFixable[$namespace]) ? $this->foundFixable[$namespace] + 1 : 1;
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
