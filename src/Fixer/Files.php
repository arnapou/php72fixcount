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

class Files implements \IteratorAggregate
{
    /**
     * @var string[]
     */
    private $extensions;
    /**
     * @var string
     */
    private $path;
    /**
     * @var string
     */
    private $php72fixcountBasedir;

    /**
     * PhpFiles constructor.
     * @param string $path
     * @param array  $extensions
     */
    public function __construct($path, $extensions)
    {
        $this->path                 = $path;
        $this->extensions           = $extensions;
        $this->php72fixcountBasedir = \dirname(\dirname(__DIR__));
    }

    /**
     * @return \CallbackFilterIterator
     */
    public function getIterator()
    {
        return new \CallbackFilterIterator(
            new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path)),
            /**
             * @param \SplFileInfo $file
             * @param string       $key
             * @param \Traversable $iterator
             * @return bool
             */
            function (\SplFileInfo $file, $key, \Traversable $iterator) {
                return !(
                    /* directories ignored */
                    !$file->isFile() ||
                    /* extension not allowed */
                    !\in_array(pathinfo($file->getPathname(), PATHINFO_EXTENSION), $this->extensions, true) ||
                    /* ignore php71fixcount folder */
                    ($this->php72fixcountBasedir && 0 === strpos($file->getRealPath(), "$this->php72fixcountBasedir/")) ||
                    /* ignore php71fixcount generated files (security if $php72fixcount_basedir is empty) */
                    \in_array($file->getBasename(), ['generated.php72fix.count.php', 'generated.php72fix.sizeof.php'], true)
                );
            }
        );
    }
}
