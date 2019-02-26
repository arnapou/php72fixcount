<?php

namespace Arnapou\Php72FixCount\Fixer;

use Arnapou\Php72FixCount\Php72;

class Fixer
{
    use TargetTrait;

    /**
     * @var array
     */
    private $paths;
    /**
     * @var array
     */
    private $options;

    /**
     * Fixer constructor.
     * @param array $paths
     * @param array $options
     */
    public function __construct(array $paths, array $options = [])
    {
        $this->paths   = $paths;
        $this->options = $options + ShellArguments::getDefaultOptions();
        $this->execute();
    }

    /**
     */
    public function execute()
    {
        foreach ($this->paths as $path) {
            if (is_dir($path)) {
                foreach (new Files($path, ['php']) as $file) {
                    $this->analyze($file->getPathname(), $this->options);
                }
            } elseif (is_file($path)) {
                $this->analyze($path, $this->options);
            }
        }

        foreach ($this->targets as $target) {
            $this->unfixable[$target] = array_diff_key($this->unfixable[$target], $this->fixable[$target]);
            $this->fixable[$target]   = array_diff_key($this->fixable[$target], $this->conflicts[$target]);
        }
    }

    /**
     * @param string $filename
     * @param array  $options
     */
    protected function analyze($filename, array $options)
    {
        $parser = new Parser($filename);

        foreach ($this->targets as $target) {
            foreach ($parser->getConflicts($target) as $ns => $count) {
                if (!$options['quiet']) {
                    echo "$target | $filename | CONFLICT | $ns\n";
                }
                $this->conflicts[$target][$ns] = isset($this->conflicts[$target][$ns]) ? $this->conflicts[$target][$ns] + $count : $count;
            }

            foreach ($parser->getFixable($target) as $ns => $count) {
                if (!$options['quiet']) {
                    $s = $count == 1 ? ' ' : 's';
                    echo "$target | $filename | " . str_pad($count, 2, ' ', STR_PAD_LEFT) . " call$s" . " | $ns\n";
                }
                $this->fixable[$target][$ns] = isset($this->fixable[$target][$ns]) ? $this->fixable[$target][$ns] + $count : $count;
            }

            foreach ($parser->getUnfixable($target) as $ns => $count) {
                $this->unfixable[$target][$ns] = isset($this->unfixable[$target][$ns]) ? $this->unfixable[$target][$ns] + $count : $count;
            }
        }
    }

    /**
     * @param string $target
     * @param string $outputFile
     */
    public function writeTo($target, $outputFile)
    {
        if (!\in_array($target, $this->targets)) {
            throw new \InvalidArgumentException('Target argument is invalid, correct values : ' . implode(', ', $this->targets));
        }

        ksort($this->fixable[$target]);
        ksort($this->unfixable[$target]);
        ksort($this->conflicts[$target]);

        $date         = date('l d F Y H:i:s');
        $phpFixed     = self::getPhpFixedNamespaces(array_keys($this->fixable[$target]), $target) ?: '// nothing is fixable';
        $phpUnfixed   = self::getPhpUnfixedNamespaces(array_keys($this->unfixable[$target])) ?: '// nothing to list';
        $phpConflicts = self::getPhpUnfixedNamespaces(array_keys($this->conflicts[$target])) ?: '// nothing to list';

        file_put_contents($outputFile,
            "<?php

/*
 * Generated: $date
 */

$phpFixed

/* 
 * Bellow are not fixed because \\$target is called
 * or 'use function count' is used at the beginning
 */

$phpUnfixed

/* 
 * Bellow are not fixed because there is a conflict 
 * with an existing namespaced $target function
 */

$phpConflicts

",
            LOCK_EX);
    }

    /**
     * @param array  $namespaces
     * @param string $target
     * @return string
     */
    protected function getPhpFixedNamespaces(array $namespaces, $target)
    {
        $max = 0;
        $php = '';
        foreach ($namespaces as $namespace) {
            $n   = \strlen($namespace);
            $max = $n > $max ? $n : $max;
        }
        $class = Php72::class;
        foreach ($namespaces as $namespace) {
            $namespace = str_pad($namespace, $max + 1, ' ', STR_PAD_RIGHT);
            $php       .= "namespace $namespace { function $target(\$item, \$mode = \\COUNT_NORMAL) { return \\$class::count(\$item, \$mode); } }\n";
        }
        return $php;
    }

    /**
     * @param array $namespaces
     * @return string
     */
    protected function getPhpUnfixedNamespaces(array $namespaces)
    {
        $php = '';
        foreach ($namespaces as $namespace) {
            $php .= "// namespace $namespace\n";
        }
        return $php;
    }
}
