<?php

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

$PHP72FIXCOUNT_TARGET = $PHP72FIXCOUNT_TARGET ?? 'count';
$generatedFile        = __DIR__ . "/generated.php72fix.$PHP72FIXCOUNT_TARGET.php";

switch ($arguments->getCommand()) {
    case 'clean':
        file_put_contents($generatedFile, "<php \n", LOCK_EX);
        break;
    case 'generate':
        $fixer = new Fixer($PHP72FIXCOUNT_TARGET);
        $fixer->execute($arguments->getPaths(), $generatedFile, $arguments->getOptions());
        break;
}
