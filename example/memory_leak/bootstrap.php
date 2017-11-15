<?php
include __DIR__ . "/../../vendor/autoload.php";

$blocks = [];

function alloc()
{
    global $blocks;

    $blocks[] = str_repeat(" ", 1024 * 1024 * 10);

    print(count($blocks) . "\n");
}