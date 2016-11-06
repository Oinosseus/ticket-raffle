<?php

/////////////////////////////////////////////////////////////////////////////
// Page Load
//
// This script sets the constant 'USER_PAGE' with the filename
// of the pages/** script that contains the content.
// A "session_start()" must be called before!
//
// To set a new page send a HTTP request with
// variable "USER_PAGE" set.


// check for new page request
if (isset($_REQUEST['USER_PAGE'])) {
    $_SESSION['USER_PAGE'] = "";
    
    // switch for requested page
    switch ($_REQUEST['USER_PAGE']) {
    
        case "PASSWORD":
            $_SESSION['USER_PAGE'] = "password_gen.php";
            break;
            
        case "HOME":
        default:
            $_SESSION['USER_PAGE'] = "home.php";
    }

// set session variable if not set
} else if (!!!isset($_SESSION['USER_PAGE'])) {
$_SESSION['USER_PAGE'] = "home.php";
}

// set page
define("USER_PAGE", $_SESSION['USER_PAGE']);

?>
