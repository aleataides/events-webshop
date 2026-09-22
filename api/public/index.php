<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../bootstrap/container.php';
$app = (require __DIR__ . '/../bootstrap/app.php')($container);
$app->run();
