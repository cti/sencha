<?php

$loader = include __DIR__.'/vendor/autoload.php';

$filesystem = new \Symfony\Component\Filesystem\Filesystem();
$filesystem->remove(implode(DIRECTORY_SEPARATOR, array(__DIR__, 'tests', 'build')));


$loader->add("Bootstrap", __DIR__.'/tests/src/php/Bootstrap');
$loader->add("Command", __DIR__.'/tests/src/php/Command');
$loader->add("Controller", __DIR__.'/tests/src/php/Controller');
$loader->add("Build", __DIR__.'/tests/build/php/Build');
