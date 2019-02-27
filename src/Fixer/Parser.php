<?php

namespace Arnapou\Php72FixCount\Fixer;

class Parser
{
    use TargetTrait;
    /**
     * @var string
     */
    private $filename;

    /**
     * Parser constructor.
     * @param string $filename
     */
    public function __construct($filename)
    {
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
        foreach ($reducedTokens as list($type, $value)) {
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
                    $target = strtolower($value[1]);
                    if (\in_array($target, $this->targets)) {
                        if (strtolower($value[0]) === $target) {
                            $useFunctionNative = true;
                        } else {
                            $this->addConflict($target, $namespace);
                        }
                    }
                } elseif (ReducedTokens::T_CLASS === $type || ReducedTokens::T_TRAIT === $type) {
                    $class      = $value;
                    $classBrace = $braces;
                } elseif (ReducedTokens::T_FUNCTION === $type) {
                    $function      = $value;
                    $functionBrace = $braces;
                    $target        = strtolower($value);
                    if (!$class && \in_array($target, $this->targets)) {
                        $this->addConflict($target, $namespace);
                    }
                } elseif (ReducedTokens::T_FUNCTION_CALL === $type) {
                    $target = strtolower($value);
                    if (\in_array($target, $this->targets)) {
                        if ($useFunctionNative) {
                            $this->addUnfixable($target, $namespace);
                        } else {
                            $this->addFixable($target, $namespace);
                        }
                    } elseif (\in_array($target, $this->backslashTargets)) {
                        $this->addUnfixable(ltrim($target, '\\'), $namespace);
                    }
                }
            }
        }
    }

    /**
     * @param string $target
     * @param string $namespace
     */
    private function addConflict($target, $namespace)
    {
        $this->conflicts[$target][$namespace] = isset($this->conflicts[$target][$namespace]) ? $this->conflicts[$target][$namespace] + 1 : 1;
    }

    /**
     * @param string $target
     * @param string $namespace
     */
    private function addFixable($target, $namespace)
    {
        $this->fixable[$target][$namespace] = isset($this->fixable[$target][$namespace]) ? $this->fixable[$target][$namespace] + 1 : 1;
    }

    /**
     * @param string $target
     * @param string $namespace
     */
    private function addUnfixable($target, $namespace)
    {
        $this->unfixable[$target][$namespace] = isset($this->unfixable[$target][$namespace]) ? $this->unfixable[$target][$namespace] + 1 : 1;
    }
}
