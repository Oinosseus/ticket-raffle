<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// session_start();

// load config
include("config.php");

// load classes
include("classes/raffle.php");
include("classes/participation.php");
include("classes/participant.php");
include("classes/database_wrapper.php");

// create new database wrapper
$DB = new DatabaseWrapper(CONFIG_DATABASE_FILE);

// check tables
$DB->checkStructure();

// process maintenance
include("modules/maintenance.php");


?>
