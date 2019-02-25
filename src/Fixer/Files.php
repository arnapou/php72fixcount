<?php

namespace Arnapou\Php72FixCount\Fixer;

class Files implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $extensions;
    /**
     * @var string
     */
    private $path;

    /**
     * PhpFiles constructor.
     * @param string $path
     * @param array  $extensions
     */
    public function __construct($path, $extensions)
    {
        $this->path       = $path;
        $this->extensions = $extensions;
    }

    /**
     * @return \SplFileInfo[]|\CallbackFilterIterator
     */
    public function getIterator()
    {
        $php72fixcount_basedir = realpath(__DIR__ . '/../..');
        return new \CallbackFilterIterator(
            new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path)),
            function (\SplFileInfo $file, $key, $iterator) use ($php72fixcount_basedir) {
                if (/* directories ignored */
                    !$file->isFile() ||
                    /* extension not allowed */
                    !\in_array(pathinfo($file->getPathname(), PATHINFO_EXTENSION), $this->extensions) ||
                    /* ignore php71fixcount folder */
                    $php72fixcount_basedir && strpos($file->getRealPath(), "$php72fixcount_basedir/") === 0 ||
                    /* ignore php71fixcount generated files (security if $php72fixcount_basedir is empty) */
                    \in_array($file->getBasename(), ['generated.php72fix.count.php', 'generated.php72fix.sizeof.php'])
                ) {
                    return false;
                }
                return true;
            }
        );
    }
}
