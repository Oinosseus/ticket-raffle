<?php

// load classes
include("modules/config.php");
include("classes/raffle.php");
include("classes/drawing.php");
include("classes/database_wrapper.php");

// create new database wrapper
$DB = new DatabaseWrapper(CONFIG_DATABASE_FILE);

// check tables
$DB->checkStructure();

// process maintenance
include("modules/maintenance.php");


?>
