<?php

// actual date
$now = new DateTime();
$now->setTimezone(new DateTimeZone(CONFIG_TIMEZONE));



// ---------------------------------------------------------------------------
//                                 Enable Logging
// ---------------------------------------------------------------------------

# open and lock file
$something_was_written = false;
$file_handle = fopen(CONFIG_MAINTENANCELOG, "a+");
flock($file_handle, LOCK_EX) or exit("Error: could not lock file '" . CONFIG_MAINTENANCELOG . "'!");

function logaction($message) {
    global $file_handle;
    global $something_was_written;
    global $now;

    # ensure newline at the end
    if (substr($message, strlen($message), 1) != "\n") $message .= "\n";

    # write log header
    if ($something_was_written == false) {
        fwrite($file_handle, "\n\nMaintenance Did Something\n");
        fwrite($file_handle,     "=========================\n\n");
        fwrite($file_handle, $now->format('Y-m-d H:i:s') . "\n\n");
    }

    fwrite($file_handle, $message . "\n");
}



// ---------------------------------------------------------------------------
//                               Opening Raffles
// ---------------------------------------------------------------------------

// check all raffles
foreach ($DB->getRaffles() as $raffle) {

    // only care for COMMITTED raffles
    if ($raffle->getState() === Raffle::STATE_COMMITTED) {

        // only care if OpenTime is passed
        if ($raffle->getOpenTime() <= $now) {

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
        if ($raffle->getCloseTime() <= $now) {

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

fclose($file_handle);


?>
