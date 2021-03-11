<?php

/*
 * This file is part of the Arnapou Php72FixCount package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

\call_user_func(
    static function () {
        foreach (['count', 'sizeof'] as $target) {
            if (!is_file(__DIR__ . "/generated.php72fix.$target.php") &&
                !touch(__DIR__ . "/generated.php72fix.$target.php")
            ) {
                trigger_error("File generated.php72fix.$target.php was not found and could not be created.", E_USER_WARNING);
            }

            include __DIR__ . "/generated.php72fix.$target.php";
        }
    }
);
