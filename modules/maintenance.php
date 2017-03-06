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
    if (substr($message, strlen($message) - 1, 1) != "\n") $message .= "\n";

    # write log header
    if ($something_was_written == false) {
        fwrite($file_handle, "\n\nMaintenance Did Something\n");
        fwrite($file_handle,     "=========================\n\n");
        fwrite($file_handle, $now->format('Y-m-d H:i:s') . "\n\n");
    }

    fwrite($file_handle, $message);
    $something_was_written = true;
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

            // set state to closed
            $raffle->setState(Raffle::STATE_CLOSED);
            $raffle->save();

            // vote participations
            foreach ($DB->getParticipations($raffle) as $pn) {

                // only if participation valid for voting
                if (in_array($pn->getState(), array(Participation::STATE_ENTRY_ACCEPTED, Participation::STATE_DECLINE_REQUESTED))) {

                    // vote
                    $pn->setResultingParticipations($pn->getParticipant()->countParticipations() + 1);
                    $pn->setResultingWins($pn->getParticipant()->countWins());
                    $pn->setResultingRandom(rand(0,10000));
                    $pn->setResultingScore($pn->getResultingRandom() * ($pn->getResultingParticipations() - $pn->getResultingWins()) / $pn->getResultingParticipations());
                    $pn->setState(Participation::STATE_VOTED);
                    $pn->save();

                    // log
                    $msg  = "    Voting Participation for " . $pn->getParticipant()->getEmail() . ":\n";
                    $msg .= "        Previous Participations = " . $pn->getResultingParticipations() . "\n";
                    $msg .= "        Previous Wins           = " . $pn->getResultingWins() . "\n";
                    $msg .= "        Random                  = " . $pn->getResultingRandom() . "\n";
                    $msg .= "        Score                   = " . $pn->getResultingScore();
                    logaction($msg);

                }
            }
        }

    }

}



// ---------------------------------------------------------------------------
//                                 Close Log
// ---------------------------------------------------------------------------

fclose($file_handle);


?>
