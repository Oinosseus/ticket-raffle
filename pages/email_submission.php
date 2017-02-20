<?php


// user verification key
$UserVerifKey = "";
if (isset($_REQUEST['UserVerificationKey'])) $UserVerifKey = trim($_REQUEST['UserVerificationKey']);

// drawing id
$DrawingId = 0;
if (isset($_REQUEST['DrawingId'])) $DrawingId = intval($_REQUEST['DrawingId']);

// try to find drawing
$drawing = Null;
foreach ($DB->getDrawings() as $d) {
    if ($d->getId() == $DrawingId) {
        $drawing = $d;
        break;
    }
}

// drawing not found
if ($drawing == Null) {
    echo '<div class="message error">Ziehung konnte nicht gefunden werden!</div>';

// invalid key
} else if ($drawing->getUserVerificationKey() != $UserVerifKey) {
    echo '<div class="message error">Ung&uuml;ltiger Best&auml;tigungscode!</div>';

// drawing and key are valid
} else {

    // make existing key invalid
    $drawing->createUserVerificationKey();
    $drawing->save();

    // enter drawing
    if ($drawing->getState() == Drawing::STATE_ENTRY_REQUESTED) {

        // raffle is not in committed state
        if ($drawing->getRaffle()->getState() != Raffle::STATE_OPEN) {
            echo '<div class="message error">An der Ziehung kann nicht mehr teilgenommen werden!</div>';

        // raffle is in committed state
        } else {
            $drawing->setState(Drawing::STATE_ENTRY_ACCEPTED);
            $drawing->save();
            echo '<div class="message success">Sie sind der Ziehung ' . $drawing->getRaffle()->getName() . ' beigetreten.</div>';
        }
    }
}



?>
