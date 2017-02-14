<?php

include("modules/config.php");
include("classes/raffle.php");
include("classes/drawing.php");
include("classes/database_wrapper.php");

// create new database wrapper
$DB = new DatabaseWrapper(CONFIG_DATABASE_FILE);

// check tables
$DB->checkStructure();

// actual date
$NOW = new DateTime();
$NOW->setTimezone(new DateTimeZone(CONFIG_TIMEZONE));



// ---------------------------------------------------------------------------
//                                 Enable Logging
// ---------------------------------------------------------------------------

# open and lock file
$SOMETHING_WAS_WRIITEN = false;
$FILE_HANDLE = fopen(CONFIG_MAINTENANCELOG, "a+");
flock($FILE_HANDLE, LOCK_EX) or exit("Error: could not lock file '" . CONFIG_MAINTENANCELOG . "'!");

function logaction($message) {
    global $FILE_HANDLE;
    global $SOMETHING_WAS_WRIITEN;
    global $NOW;

    # ensure newline at the end
    if (substr($message, strlen($message), 1) != "\n") $message .= "\n";

    # write log header
    if ($SOMETHING_WAS_WRIITEN == false) {
        fwrite($FILE_HANDLE, "\n\nMaintenance Did Something\n");
        fwrite($FILE_HANDLE,     "=========================\n\n");
        fwrite($FILE_HANDLE, $NOW->format('Y-m-d H:i:s') . "\n\n");
    }

    fwrite($FILE_HANDLE, $message . "\n");
    echo $message;
}



// ---------------------------------------------------------------------------
//                               Opening Raffles
// ---------------------------------------------------------------------------

// check all raffles
foreach ($DB->getRaffles() as $raffle) {

    // only care for COMMITTED raffles
    if ($raffle->getState() === Raffle::STATE_COMMITTED) {

        // only care if OpenTime is passed
        if ($raffle->getOpenTime() <= $NOW) {

            // user info
            logaction("Opening raffle #" . $raffle->getId() . " '" . $raffle->getName() . "'\n");

            // set state to open
            $raffle->setState(Raffle::STATE_OPEN);
            $raffle->save();
        }

    }

}



// ---------------------------------------------------------------------------
//                               Closing Raffles
// ---------------------------------------------------------------------------


// check all raffles
foreach ($DB->getRaffles() as $raffle) {

    // only care for OPEN raffles
    if ($raffle->getState() === Raffle::STATE_OPEN) {

        // only care if OpenTime is passed
        if ($raffle->getCloseTime() <= $NOW) {

            // user info
            logaction("Closing raffle #" . $raffle->getId() . " '" . $raffle->getName() . "'\n");

            // set state to open
            $raffle->setState(Raffle::STATE_CLOSED);
            $raffle->save();
        }

    }

}



// ---------------------------------------------------------------------------
//                                 Close Log
// ---------------------------------------------------------------------------

fclose($FILE_HANDLE);


?>
