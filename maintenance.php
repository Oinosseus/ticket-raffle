<?php

include("modules/config.php");
include("classes/database_wrapper.php");

// create new database wrapper
$DB = new DatabaseWrapper(CONFIG_DATABASE_FILE, false, true);

// check tables
$DB->checkStructure();

?>