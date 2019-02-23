<?php

namespace Arnapou\Php72FixCount\Fixer;

use Arnapou\Php72FixCount\Php72;

class Fixer
{
    /**
     * @var array
     */
    private $fixable = [];
    /**
     * @var array
     */
    private $unfixable = [];
    /**
     * @var array
     */
    private $conflicts = [];

    /**
     * @param array  $paths
     * @param array  $options
     * @param string $outputFile
     */
    public function execute(array $paths, $outputFile, array $options = [])
    {
        $options         = $options + ShellArguments::getDefaultOptions();
        $this->fixable   = [];
        $this->unfixable = [];
        $this->conflicts = [];

        foreach ($paths as $path) {
            if (is_dir($path)) {
                foreach (new Files($path, ['php']) as $file) {
                    $this->analyze($file->getPathname(), $options);
                }
            } elseif (is_file($path)) {
                $this->analyze($path, $options);
            }
        }

        $this->unfixable = array_diff_key($this->unfixable, $this->fixable);
        $this->fixable   = array_diff_key($this->fixable, $this->conflicts);

        $this->writeTo($outputFile);
    }

    /**
     * @param string $filename
     * @param array  $options
     */
    protected function analyze($filename, array $options)
    {
        $parser = new Parser($filename);

        foreach ($parser->getConflicts() as $ns => $count) {
            if (!$options['quiet']) {
                echo "$filename | CONFLICT | $ns\n";
            }
            $this->conflicts[$ns] = isset($this->conflicts[$ns]) ? $this->conflicts[$ns] + $count : $count;
        }

        foreach ($parser->getFixable() as $ns => $count) {
            if (!$options['quiet']) {
                $s = $count == 1 ? ' ' : 's';
                echo "$filename | " . str_pad($count, 2, ' ', STR_PAD_LEFT) . " call$s" . " | $ns\n";
            }
            $this->fixable[$ns] = isset($this->fixable[$ns]) ? $this->fixable[$ns] + $count : $count;
        }

        foreach ($parser->getUnfixable() as $ns => $count) {
            $this->unfixable[$ns] = isset($this->unfixable[$ns]) ? $this->unfixable[$ns] + $count : $count;
        }
    }

    /**
     * @param string $outputFile
     */
    protected function writeTo($outputFile)
    {
        ksort($this->fixable);
        ksort($this->unfixable);
        ksort($this->conflicts);

        $date         = date('l d F Y H:i:s');
        $phpFixed     = self::getPhpFixedNamespaces(array_keys($this->fixable)) ?: '// nothing is fixable';
        $phpUnfixed   = self::getPhpUnfixedNamespaces(array_keys($this->unfixable)) ?: '// nothing to list';
        $phpConflicts = self::getPhpUnfixedNamespaces(array_keys($this->conflicts)) ?: '// nothing to list';

        file_put_contents($outputFile, "<?php

/*
 * Generated: $date
 */

$phpFixed

/* 
 * Bellow are not fixed because \\count is called
 */

$phpUnfixed

/* 
 * Bellow are not fixed because there is a conflict 
 * with an existing namespaced count function
 */

$phpConflicts

", LOCK_EX);

    }

    /**
     * @param array $namespaces
     * @return string
     */
    protected function getPhpFixedNamespaces(array $namespaces)
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
            $php       .= "namespace $namespace { function count(\$item, \$mode = \\COUNT_NORMAL) { return \\$class::count(\$item, \$mode); } }\n";
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
