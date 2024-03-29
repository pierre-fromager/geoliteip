<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('date.timezone', 'Europe/Paris');
ini_set('register_globals', 0);
ini_set('opcache.enable', 0);
ini_set('memory_limit', '256M');

echo 'Booting...' . "\n";
$there = __DIR__;
$loader = require $there . '/../vendor/autoload.php';
$loader->add('Tests', $there);
echo 'Testing...' . "\n";