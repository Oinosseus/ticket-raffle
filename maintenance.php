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
//                               Opening Raffles
// ---------------------------------------------------------------------------


// check all raffles
foreach ($DB->getRaffles() as $raffle) {

    // only care for COMMITTED raffles
    if ($raffle->getState() === Raffle::STATE_COMMITTED) {

        // only care if OpenTime is passed
        if ($raffle->getOpenTime() <= $NOW) {

            // user info
            echo "Opening raffle #" . $raffle->getId() . " '" . $raffle->getName() . "'\n";

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
            echo "Closing raffle #" . $raffle->getId() . " '" . $raffle->getName() . "'\n";

            // set state to open
            $raffle->setState(Raffle::STATE_CLOSED);
            $raffle->save();
        }

    }

}


?>
