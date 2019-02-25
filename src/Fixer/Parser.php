<?php

namespace Arnapou\Php72FixCount\Fixer;

class Parser
{
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
     * @var string
     */
    private $target;
    /**
     * @var string
     */
    private $filename;

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
        $this->target   = $target;
        $this->filename = $filename;
        $this->parse();
    }

    private function parse()
    {
        $reducedTokens     = new ReducedTokens(token_get_all(file_get_contents($this->filename)));
        $braces            = 0;
        $namespace         = '';
        $class             = '';
        $classBrace        = -1;
        $function          = '';
        $functionBrace     = -1;
        $useFunctionNative = false;
        foreach ($reducedTokens as $type => $value) {
            if (ReducedTokens::T_BRACE_OPEN === $type) {
                $braces++;
            } elseif (ReducedTokens::T_BRACE_CLOSE === $type) {
                $braces--;
                if ($class && $classBrace === $braces) {
                    $class = '';
                }
                if ($function && $functionBrace === $braces) {
                    $function = '';
                }
            } elseif (ReducedTokens::T_NAMESPACE === $type) {
                $namespace         = $value;
                $useFunctionNative = false;
            } elseif ($namespace) {
                if (ReducedTokens::T_USE_FUNCTION === $type) {
                    if (strtolower($value[1]) === $this->target) {
                        if (strtolower($value[0]) === $this->target) {
                            $useFunctionNative = true;
                            $this->addUnfixable($namespace);
                        } else {
                            $this->addConflict($namespace);
                        }
                    }
                } elseif (ReducedTokens::T_CLASS === $type || ReducedTokens::T_TRAIT === $type) {
                    $class      = $value;
                    $classBrace = $braces;
                } elseif (ReducedTokens::T_FUNCTION === $type) {
                    $function      = $value;
                    $functionBrace = $braces;
                    if (!$class && strtolower($value) === $this->target) {
                        $this->addConflict($namespace);
                    }
                } elseif (ReducedTokens::T_FUNCTION_CALL === $type) {
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
