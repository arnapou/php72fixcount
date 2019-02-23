<?php

\call_user_func(function () {
    foreach (['count', 'sizeof'] as $target) {

        if (!is_file(__DIR__ . "/generated.php72fix.$target.php")) {
            if (!touch(__DIR__ . "/generated.php72fix.$target.php")) {
                trigger_error("File generated.php72fix.$target.php was not found and could not be created.", E_USER_WARNING);
            }
        }

        include __DIR__ . "/generated.php72fix.$target.php";

    }
});
