<?php

//
// --------------------------------------------------------------------------
//                             Admin Authentication
// --------------------------------------------------------------------------
//
// This script sets the constant 'USER_IS_ADMIN'.
// The constant is true or false.
// A "session_start()" must be called before!
//
// A login can be performed by sending a HTTP post request with
// variable "ADMIN_LOGIN" and the passwort as content.
//
// For logout sending an HTTP post or get request
// with variable "ADMIN_LOGOUT" and any content.

// check for logout
if (isset($_REQUEST['ADMIN_LOGOUT'])) {
    $_SESSION['USER_IS_ADMIN'] = false;

// check for login
} else if (isset($_POST['ADMIN_LOGIN'])) {
    if (password_verify($_POST['ADMIN_LOGIN'], CONFIG_ADMIN_PASSWORD)) {
        $_SESSION['USER_IS_ADMIN'] = true;
    } else {
        $_SESSION['USER_IS_ADMIN'] = false;
    }

// re-login
} else {
    if (isset($_SESSION['USER_IS_ADMIN'])) {
    } else {
        $_SESSION['USER_IS_ADMIN'] = false;
    }
}

// set admin rights
define("USER_IS_ADMIN", $_SESSION['USER_IS_ADMIN']);

?>
