<?php // -*- coding: utf-8-unix -*-

require('vendor/autoload.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel to STDERR
$log = new Logger('name');
$log->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));

// add records to the log
$log->addWarning("Running a beta version of Heroku's PHP support");

echo "Hello, world!";
