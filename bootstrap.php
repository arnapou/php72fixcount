<?php

if (!is_file(__DIR__ . '/generated.php72fixcount.php')) {
    touch(__DIR__ . '/generated.php72fixcount.php');
}

if (!is_file(__DIR__ . '/generated.php72fixcount.php')) {
    trigger_error('File generated.php72fixcount.php was not found.', E_USER_WARNING);
} else {
    include __DIR__ . '/generated.php72fixcount.php';
}
