<?php

/*
 * This file is part of the Arnapou Php71FixCount package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Arnapou\Php72FixCount\Fixer\Fixer;
use Arnapou\Php72FixCount\Fixer\ShellArguments;

/*
 * Autoload
 */
spl_autoload_register(function ($class) {
    $baseNS = 'Arnapou\\Php72FixCount\\';
    if (0 === strpos($class, $baseNS)) {
        $path     = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        $filename = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, \strlen($baseNS))) . '.php';
        if (is_file($path . $filename)) {
            include $path . $filename;
        }
    }
});

/*
 * Execute script
 */
$arguments = new ShellArguments(isset($argv) ? \array_slice($argv, 1) : []);

if (PHP_VERSION_ID < 70200) {
    $arguments->usage('Php version < 7.2 : this script is useless');
}

switch ($arguments->getCommand()) {
    case 'clean':
        foreach (['count', 'sizeof'] as $target) {
            file_put_contents(__DIR__ . "/generated.php72fix.$target.php", "<php \n", LOCK_EX);
        }
        break;
    case 'generate':
        $fixer = new Fixer($arguments->getPaths(), $arguments->getOptions());
        foreach (['count', 'sizeof'] as $target) {
            $fixer->writeTo($target, __DIR__ . "/generated.php72fix.$target.php");
        }
        break;
    case 'search':
        $fixer = new Fixer($arguments->getPaths(), $arguments->getOptions());
        break;
}
