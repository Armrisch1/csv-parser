<?php
require_once realpath('vendor/autoload.php');
use src\core\Db;

$config = require 'src/config/db.php';
Db::initialize($config);