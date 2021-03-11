<?php

/*
 * This file is part of the Arnapou Php71FixCount package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    /**
     * @return void
     */
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
            if (ReducedTokens::T_NAMESPACE === $type) {
                $namespace         = $value;
                $useFunctionNative = false;
            } elseif ($namespace) {
                switch ($type) {
                    case ReducedTokens::T_BRACE_OPEN:
                        $braces++;
                        break;
                    case ReducedTokens::T_BRACE_CLOSE:
                        $braces--;
                        if ($class && $classBrace === $braces) {
                            $class = '';
                        }
                        if ($function && $functionBrace === $braces) {
                            $function = '';
                        }
                        break;
                    case ReducedTokens::T_USE_FUNCTION:
                        $target = strtolower($value[1]);
                        if (\in_array($target, $this->targets, true)) {
                            if (strtolower($value[0]) === $target) {
                                $useFunctionNative = true;
                            } else {
                                $this->addConflict($target, $namespace);
                            }
                        }
                        break;
                    case ReducedTokens::T_CLASS:
                    case ReducedTokens::T_TRAIT:
                        $class      = $value;
                        $classBrace = $braces;
                        break;
                    case ReducedTokens::T_FUNCTION:
                        $function      = $value;
                        $functionBrace = $braces;
                        $target        = strtolower($value);
                        if (!$class && \in_array($target, $this->targets, true)) {
                            $this->addConflict($target, $namespace);
                        }
                        break;
                    case ReducedTokens::T_FUNCTION_CALL:
                        $target = strtolower($value);
                        if (\in_array($target, $this->targets, true)) {
                            if ($useFunctionNative) {
                                $this->addUnfixable($target, $namespace);
                            } else {
                                $this->addFixable($target, $namespace);
                            }
                        } elseif ('\\' === $target[0]) {
                            $target = substr($target, 1);
                            if (\in_array($target, $this->targets, true)) {
                                $this->addUnfixable($target, $namespace);
                            }
                        }
                        break;
                }
            }
        }
    }

    /**
     * @param 'count'|'sizeof' $target
     * @param string $namespace
     *
     * @return void
     */
    private function addConflict($target, $namespace)
    {
        $this->conflicts[$target][$namespace] = isset($this->conflicts[$target][$namespace]) ? $this->conflicts[$target][$namespace] + 1 : 1;
    }

    /**
     * @param 'count'|'sizeof' $target
     * @param string $namespace
     *
     * @return void
     */
    private function addFixable($target, $namespace)
    {
        $this->fixable[$target][$namespace] = isset($this->fixable[$target][$namespace]) ? $this->fixable[$target][$namespace] + 1 : 1;
    }

    /**
     * @param 'count'|'sizeof' $target
     * @param string $namespace
     *
     * @return void
     */
    private function addUnfixable($target, $namespace)
    {
        $this->unfixable[$target][$namespace] = isset($this->unfixable[$target][$namespace]) ? $this->unfixable[$target][$namespace] + 1 : 1;
    }
}
