<?php

define('TIME_START', microtime(true));

require_once 'autoload.php';

$config = require_once 'config.php';
$lang = require_once 'lang/' . $config['lang'] . '.php';

$config['base_path'] = dirname($_SERVER['SCRIPT_NAME']);

date_default_timezone_set($config['timezone']);
