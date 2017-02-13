<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// load modules
include("modules/config.php");
include("modules/login.php");
include("modules/page.php");

// load classes
include("classes/raffle.php");
include("classes/drawing.php");
include("classes/database_wrapper.php");

// create a read-only database wrapper object
$DB = new DatabaseWrapper(CONFIG_DATABASE_FILE);

?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>Ticket Raffle</title>
        <link rel="stylesheet" type="text/css" href="template/style.css">
    </head>
    <body>
        <div id="headerimage"><img src="template/header.png"></div>

        <div id="navigation">
            <ul>
                <li>
                    <label>Navigation</label>
                    <ul>
                        <li><a href="index.php?USER_PAGE=HOME">Startseite</a></li>
                        <li><a href="index.php?USER_PAGE=RAFFLES">Verlosungen</a></li>
                    </ul>
                </li>
                <li>
                    <label>Administration</label>
                    <ul>
                        <li><a href="index.php?USER_PAGE=PASSWORD">Password-Gen</a></li>
                        <?php if (USER_IS_ADMIN) : ?>
                            <li><a href="index.php?USER_PAGE=NEW_RAFFLE">Neue Verlosung</a></li>
                            <li><a href="?ADMIN_LOGOUT=TRUE">Logout</a></li>
                        <?php else : ?>
                            <li>
                                <form action="index.php" method="post">
                                    <input type="password" name="ADMIN_LOGIN">
                                    <button type="submit">Login</button>
                                </form>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <li>
                    <label>Hilfe</label>
                    <ul>
                        <li><a href="index.php?USER_PAGE=DATABSE_CHART">Datenbakstruktur</a></li>
                        <li><a href="index.php?USER_PAGE=STATE_CHART">Zustandsdiagramm</a></li>
                        <li><a href="doc/doxy/html" target="_blank">Source Doku</a></li>
                        <li><a href="index.php?USER_PAGE=LICENSE">Lizenz</a></li>
                    </ul>
                </li>
        </div>

        <div id="content">
          <?php include("pages/" . USER_PAGE); ?>
        </div>

    </body>
<html>
