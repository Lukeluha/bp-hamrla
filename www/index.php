<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require '.maintenance.php';

$container = require __DIR__ . '/../app/bootstrap.php';


define('WWW_DIR', __DIR__);
define('IMG_DIR', __DIR__ . "/images");

$container->getByType('Nette\Application\Application')->run();
