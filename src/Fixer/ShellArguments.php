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
     * @var string
     */
    private $command = '';
    /**
     * @var array
     */
    private static $defaultOptions = [
        'quiet' => false,
        'clean' => false,
    ];
    /**
     * @var array
     */
    private static $possibleCommands = [
        'generate',
        'search',
        'clean',
    ];

    /**
     * Arguments constructor.
     * @param array $arguments
     */
    public function __construct($arguments)
    {
        $this->options = self::getDefaultOptions();

        foreach ($arguments as $argument) {
            if (0 === strpos($argument, '--')) {
                $option = $this->parseOption($argument);
                if ($option && isset(self::getDefaultOptions()[$option[0]])) {
                    $this->options[$option[0]] = $option[1];
                } else {
                    $this->usage('Unknown option ' . ($option ? $option[0] : $argument) . '.');
                }
            } elseif (empty($this->command)) {
                if (\in_array($argument, self::getPossibleCommands(), true)) {
                    $this->command = $argument;
                } else {
                    $this->usage("Unknown command $argument.");
                }
            } elseif ($this->command) {
                if (!is_dir($argument)) {
                    $this->usage("Path $argument not found.");
                } else {
                    $this->paths[] = $argument;
                }
            }
        }

        if (empty($this->command)) {
            $this->usage();
        }
        if (\in_array($this->command, ['generate', 'search']) && empty($this->paths)) {
            $this->usage();
        }
        if ($this->command === 'search') {
            $this->options['quiet'] = false;
        }
    }

    /**
     * @param string $option
     * @return array|null
     */
    private function parseOption($option)
    {
        if (preg_match('!^--([^=]+)=(.*)$!', $option, $matches)) {
            return [$matches[1], trim(trim($matches[2], "'"), '"')];
        }

        if (preg_match('!^--([^=]+)$!', $option, $matches)) {
            return [$matches[1], true];
        }

        return null;
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
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return array
     */
    public static function getDefaultOptions()
    {
        return self::$defaultOptions;
    }

    /**
     * @return array
     */
    public static function getPossibleCommands()
    {
        return self::$possibleCommands;
    }

    /**
     * @param string $error
     *
     * @return void
     */
    public function usage($error = '')
    {
        $scriptName = isset($_SERVER['PHP_SELF']) ? basename($_SERVER['PHP_SELF']) : 'php72-fix-count.php';

        echo "PHP 7.2 FIX COUNT  -  https://github.com/arnapou/php72fixcount/\n";
        echo "\n";
        if ($error) {
            echo "ERROR !!\n";
            echo "    $error\n";
            echo "\n";
        } else {
            echo "DESCRIPTION\n";
            echo "    This command generate php files which are loaded by composer in order to\n";
            echo "    fix/hack the breaking change of the count/sizeof for php 7.2+\n";
            echo "\n";
            echo "SYSNOPSIS\n";
            echo "    php $scriptName [OPTION] COMMAND DIRECTORY...\n";
            echo "\n";
            echo "OPTION\n";
            echo "    --quiet    silent mode (usefull for composer post-autoload-dump)\n";
            echo "\n";
            echo "COMMAND\n";
            echo "    generate   generate the fixes\n";
            echo "    search     search the fixes (same as generate but no write)\n";
            echo "    clean      remove the fixes\n";
            echo "\n";
            echo "EXAMPLES\n";
            echo "    php $scriptName --quiet generate src vendor\n";
            echo "    php $scriptName clean\n";
            echo "\n";
        }

        exit($error ? 1 : 0);
    }
}
