<?php

// get raffle
if (isset($_REQUEST['RAFFLE_ID'])) {
    $raffle = new Raffle(intval($_REQUEST['RAFFLE_ID']), $DB);
} else {
    $raffle = new Raffle();
}



// ----------------------------------------------------------------------------
//                        Submit New Participation
// ----------------------------------------------------------------------------

// when user is not in drawing yet
if ($raffle->getState()=="OPEN" && isset($_REQUEST['ACTION']) && $_REQUEST['ACTION']=="PARTICIPATE") {

    // get drawing
    $drawing_id = 0;
    if (isset($_REQUEST['DRAWING_ID'])) $drawing_id = intval($_REQUEST['DRAWING_ID']);
    $drawing = new Drawing($drawing_id, $DB);

    // set raffle for a new drawing
    if ($drawing->getRaffle() == Null) {
        $drawing->setRaffle($raffle);
        $drawing->save();
    }

    // get participant
    $participant = $drawing->getParticipant();

    // create new participant if not existent yet
    if ($participant == Null) {

        // retrieve email from http request
        $participant_email = "";
        if (isset($_REQUEST['PARTICIPANT_EMAIL'])) {
            $participant_email = trim($_REQUEST['PARTICIPANT_EMAIL']);
        }

        // check for valid email address
        if (!preg_match(CONFIG_ALLOWEDEMAILREGEX, $participant_email)) {
            echo '<div class="message error">Die Emailadresse "' . $participant_email . '" ist nicht erlaubt!</div>';

        // email address is valid
        } else {

            // try to find existing participant
            foreach ($DB->getParticipants() as $p) {
                if ($p->getEmail() == $participant_email) {
                    $participant = $p;
                    break;
                }
            }

            // create new participant
            if ($participant == Null) {
                $participant = new Participant(0, $DB);
                $participant->setEmail($participant_email);
                $participant->save();
                $drawing->setParticipant($participant);
                $drawing->save();
            }
        }
    }

    // if participant and drawing is known
    if ($drawing != Null && $participant != Null) {

        // if raffle is in open state
        if ($raffle->getState() == Raffle::STATE_OPEN) {

            // immediate entry if admin is logged in
            if (USER_IS_ADMIN) {
                $drawing->setState(Drawing::STATE_ENTRY_ACCEPTED);
                $drawing->save();
                echo '<div class="message success">Teilnehmer ' . $participant->getEmail()  .  ' wurde eingetragen.</div>';

            // send email submission
            } else {

                // set request entry state and send email
                if ($drawing->getState() == Drawing::STATE_NOT_IN_DB or
                    $drawing->getState() == Drawing::STATE_ENTRY_REQUESTED or
                    $drawing->getState() == Drawing::STATE_DECLINE_ACCEPTED) {

                    $drawing->setState(Drawing::STATE_ENTRY_REQUESTED);
                    $newkey = $drawing->createUserVerificationKey();
                    $drawing->save();

                    if ($drawing->sendNotification()) {
                        echo '<div class="message success">Eine Best&auml;tigungsanfrage wurde an &quot;' . $participant->getEmail()  .  '&quot; gesendet!</div>';
                    } else {
                        echo '<div class="message error">Es konnte keine Best&auml;tigungsanfrage an &quot;' . $participant->getEmail()  .  '&quot; gesendet werden!</div>';
                    }
                }
            }
        }
    }
}



// ----------------------------------------------------------------------------
//                                    Sign Out
// ----------------------------------------------------------------------------

// signout from a drawing
if ($raffle->getState()=="OPEN" && isset($_REQUEST['ACTION']) && $_REQUEST['ACTION']=="SIGNOUT") {

    // get requested drawing id
    $drawing_id = 0;
    if (isset($_REQUEST['DRAWING_ID'])) $drawing_id = intval($_REQUEST['DRAWING_ID']);

    // get drawing
    $drawing = new Drawing($drawing_id, $DB);

    // invalid drawing
    if ($drawing->getId() == 0) {
        echo '<div class="message error">Ung&uuml;ltige Ziehung!</div>';

    // valid drawing
    } else {

        // set user as forbidden if admin
        if (USER_IS_ADMIN) {
            $drawing->setState(Drawing::STATE_FORBIDDEN);
            $drawing->save();
            echo '<div class="message success">Teilnehmer wurde ausgeschlossen.</div>';

        // sign out request
        } else if (in_array($drawing->getState(), array(Drawing::STATE_ENTRY_ACCEPTED, Drawing::STATE_DECLINE_REQUESTED))) {

            // generate new sign out request
            $drawing->setState(Drawing::STATE_DECLINE_REQUESTED);
            $newkey = $drawing->createUserVerificationKey();
            $drawing->save();

            // send email notification
            if ($drawing->sendNotification()) {
                echo '<div class="message success">Eine Best&auml;tigungsanfrage wurde an &quot;' . $drawing->getParticipant()->getEmail()  .  '&quot; gesendet!</div>';
            } else {
                echo '<div class="message error">Es konnte keine Best&auml;tigungsanfrage an &quot;' . $drawing->getParticipant()->getEmail()  .  '&quot; gesendet werden!</div>';
            }
        }

    }
}

?>


<table id="raffle">
        <tr>
            <th>Id</th>
            <td><?php echo $raffle->getId() ?></td>
        </tr>
        <tr>
            <th>Name</th>
            <td><?php echo $raffle->getName() ?></td>
        </tr>
        <tr>
            <th>Winners</th>
            <td><?php echo $raffle->getWinners() ?></td>
        </tr>
        <tr>
            <th>OpenTime</th>
            <td><?php echo $raffle->getOpenTimeHuman() ?></td>
        </tr>
        <tr>
            <th>CloseTime</th>
            <td><?php echo $raffle->getCloseTimeHuman() ?></td>
        </tr>
        <tr>
            <th>DrawingTime</th>
            <td><?php echo $raffle->getDrawingTimeHuman() ?></td>
        </tr>
        <tr>
            <th>State</th>
            <td><?php echo $raffle->getState() ?></td>
        </tr>
</table>

<br><br>

<table id="raffle_participants">
    <tr>
        <th>Email</th>
        <th>State</th>
        <th>Particip.</th>
        <th>Wins</th>
        <th>Random</th>
        <th>Score</th>
    </tr>
    <?php foreach ($DB->getDrawings($raffle) as $d) : ?>
        <tr class="<?php echo $d->getState() ?>">
            <td><?php echo $d->getParticipant()->getEmail() ?></td>
            <td><?php echo $d->getState() ?></td>
            <td><?php $d->getResultingParticipations() ?></td>
            <td><?php $d->getResultingWins() ?></td>
            <td><?php $d->getResultingRandom() ?></td>
            <td><?php $d->getResultingScore() ?></td>
            <?php

                // admin can directly allow or forbid user
                if (USER_IS_ADMIN) {
                    if (in_array($d->getState(), array(Drawing::STATE_FORBIDDEN, Drawing::STATE_ENTRY_REQUESTED, Drawing::STATE_DECLINE_ACCEPTED))) {
                        echo '<td><a href="?ACTION=PARTICIPATE&DRAWING_ID=' . $d->getId() . '&RAFFLE_ID=' . $raffle->getId() . '"><img src="template/icon_user_allow.svg" width="16" title="Teilnehmer Erlauben"></a></td>';
                    } else {
                        echo '<td><a href="?ACTION=SIGNOUT&DRAWING_ID=' . $d->getId() . '&RAFFLE_ID=' . $raffle->getId() . '"><img src="template/icon_user_forbid.svg" width="16" title="Teilnehmer Ausschlie&szlig;en"></a></td>';
                    }

                // user can sign in or sign out
                } else {
                    if (in_array($d->getState(), array(Drawing::STATE_ENTRY_ACCEPTED, Drawing::STATE_DECLINE_REQUESTED))) {
                        echo '<td><a href="?ACTION=SIGNOUT&DRAWING_ID=' . $d->getId() . '&RAFFLE_ID=' . $raffle->getId() . '"><img src="template/icon_user_forbid.svg" width="16" title="Austritt beantragen."></a></td>';
                    } else if (in_array($d->getState(), array(Drawing::STATE_DECLINE_ACCEPTED, Drawing::STATE_ENTRY_REQUESTED))) {
                        echo '<td><a href="?ACTION=PARTICIPATE&DRAWING_ID=' . $d->getId() . '&RAFFLE_ID=' . $raffle->getId() . '"><img src="template/icon_user_allow.svg" width="16" title="Wiedereintrit beantragen."></a></td>';
                    } else {
                        echo '<td></td>';
                    }
                }
            ?>
        </tr>
    <?php endforeach; ?>
    <tr>
        <?php if ($raffle->getState() == Raffle::STATE_OPEN) : ?>
        <td colspan="4">
            <form action="?ACTION=PARTICIPATE" method="post">
                <input type="hidden" name="RAFFLE_ID" value="<?php echo $raffle->getId() ?>" />
                <input type="text" name="PARTICIPANT_EMAIL"><button type="submit">Teilnehmen</button>
            </form>
        </td>
        <?php endif; ?>
    </tr>
</table>
