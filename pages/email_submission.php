<?php


// user verification key
$UserVerifKey = "";
if (isset($_REQUEST['UserVerificationKey'])) $UserVerifKey = trim($_REQUEST['UserVerificationKey']);

// drawing id
$ParticipationId = 0;
if (isset($_REQUEST['ParticipationId'])) $ParticipationId = intval($_REQUEST['ParticipationId']);

// try to find drawing
$participation = Null;
foreach ($DB->getParticipations() as $d) {
    if ($d->getId() == $ParticipationId) {
        $participation = $d;
        break;
    }
}

// drawing not found
if ($participation == Null) {
    echo '<div class="message error">Ziehung konnte nicht gefunden werden!</div>';

// invalid key
} else if ($participation->getUserVerificationKey() != $UserVerifKey) {
    echo '<div class="message error">Ung&uuml;ltiger Best&auml;tigungscode!</div>';

// drawing and key are valid
} else {

    // make existing key invalid
    $participation->createUserVerificationKey();
    $participation->save();

    // enter drawing
    if ($participation->getState() == Participation::STATE_ENTRY_REQUESTED) {

        // raffle is not in committed state
        if ($participation->getRaffle()->getState() != Raffle::STATE_OPEN) {
            echo '<div class="message error">An der Ziehung kann nicht mehr teilgenommen werden!</div>';

        // raffle is in committed state
        } else {
            $participation->setState(Participation::STATE_ENTRY_ACCEPTED);
            $participation->save();
            echo '<div class="message success">Sie sind der Ziehung ' . $participation->getRaffle()->getName() . ' beigetreten.</div>';
        }

    // decline drawing
    } else if ($participation->getState() == Participation::STATE_DECLINE_REQUESTED) {

        // raffle is not in committed state
        if ($participation->getRaffle()->getState() != Raffle::STATE_OPEN) {
            echo '<div class="message error">Aus der Ziehung kann nicht mehr ausgetreten werden!</div>';

        // raffle is in committed state
        } else {
            $participation->setState(Participation::STATE_DECLINE_ACCEPTED);
            $participation->save();
            echo '<div class="message success">Sie sind aus der Ziehung ' . $participation->getRaffle()->getName() . ' ausgetreten.</div>';
        }
    }
}



?>
