<?php

foreach (['count', 'sizeof'] as $target) {

    if (!is_file(__DIR__ . "/generated.php72fix.$target.php")) {
        touch(__DIR__ . "/generated.php72fix.$target.php");
    }

    if (!is_file(__DIR__ . "/generated.php72fix.$target.php")) {
        trigger_error("File generated.php72fix.$target.php was not found.", E_USER_WARNING);
    } else {
        include __DIR__ . "/generated.php72fix.$target.php";
    }

}
