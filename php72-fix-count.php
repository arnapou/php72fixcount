<?php

use Arnapou\Php72FixCount\Fixer\Fixer;
use Arnapou\Php72FixCount\Fixer\ShellArguments;

/*
 * Autoload
 */
spl_autoload_register(function ($class) {
    $baseNS = "Arnapou\\Php72FixCount\\";
    if (0 === strpos($class, $baseNS)) {
        $path     = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        $filename = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, \strlen($baseNS))) . '.php';
        if (is_file($path . $filename)) {
            include $path . $filename;
        }
    }
});

/*
 * Execute fixer
 */
$arguments = new ShellArguments(isset($argv) ? array_slice($argv, 1) : []);

if (PHP_VERSION_ID >= 70200) {
    $fixer = new Fixer();
    $fixer->execute($arguments->getPaths(), __DIR__ . '/generated.php72fixcount.php', $arguments->getOptions());
} elseif (!$arguments->isQuiet()) {
    echo "No need to run Fixer if php version is < 7.2\n";
}
