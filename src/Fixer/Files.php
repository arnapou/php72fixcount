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
        return new \CallbackFilterIterator(
            new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path)),
            function (\SplFileInfo $file, $key, $iterator) {
                return $file->isFile() && \in_array(pathinfo($file->getPathname(), PATHINFO_EXTENSION), $this->extensions);
            }
        );
    }
}
