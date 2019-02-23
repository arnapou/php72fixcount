<?php

namespace Arnapou\Php72FixCount\Fixer;

class ShellArguments
{
    /**
     * @var array
     */
    private $paths = [];
    /**
     * @var array
     */
    private $options = [];
    /**
     * @var array
     */
    private static $defaultOptions = [
        'quiet' => false,
    ];

    /**
     * Arguments constructor.
     * @param array $arguments
     */
    public function __construct($arguments)
    {
        $this->options = self::getDefaultOptions();

        foreach ($arguments as $argument) {
            if ($argument === '--quiet') {
                $this->options['quiet'] = true;
            } elseif ($argument[0] == '-') {
                $this->usage("Unknown option $argument.");
            } elseif (!is_dir($argument)) {
                $this->usage("Path $argument not found.");
            } else {
                $this->paths[] = $argument;
            }
        }

        if (empty($this->paths)) {
            $this->usage();
        }
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return bool
     */
    public function isQuiet()
    {
        return $this->options['quiet'];
    }

    /**
     * @return array
     */
    public static function getDefaultOptions()
    {
        return self::$defaultOptions;
    }

    /**
     * @param string $error
     */
    public function usage($error = '')
    {
        if ($error) {
            echo "error: $error\n\n";
        }
        $scriptName = isset($_SERVER['PHP_SELF']) ? basename($_SERVER['PHP_SELF']) : 'php72-fix-count.php';
        echo "usage: php $scriptName [--quiet] directory [...]\n\n";
        exit;
    }
}
