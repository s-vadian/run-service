<?php

spl_autoload_register(function ($className) {
    $path = '/' . str_replace('\\', '/', $className);
    include __DIR__ . $path . '.php';
});

$add = new RunService\Application();
$add->handle($argv);

?>
