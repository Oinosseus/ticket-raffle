<?php

// get raffle
if (isset($_REQUEST['RAFFLE_ID'])) {
    $raffle = new Raffle(intval($_REQUEST['RAFFLE_ID']), $DB);
    $_SESSION['RAFFLE_ID'] = $raffle->getId();
} else if (isset($_SESSION['RAFFLE_ID'])) {
    $raffle = new Raffle($_SESSION['RAFFLE_ID'], $DB);
} else {
    $raffle = new Raffle();
}



// ----------------------------------------------------------------------------
//                        Submit New Participation
// ----------------------------------------------------------------------------

if ($raffle->getState()=="OPEN" && isset($_REQUEST['ACTION']) && $_REQUEST['ACTION']=="PARTICIPATE") {

    $participant   = Null;
    $participation = Null;

    // get participant
    if ($participant == Null && isset($_REQUEST['PARTICIPANT_EMAIL'])) {

        // get email
        $participant_email = trim($_REQUEST['PARTICIPANT_EMAIL']);

        // check for valid email address
        if (preg_match(CONFIG_ALLOWEDEMAILREGEX, $participant_email)) {
            $participant_email = trim($_REQUEST['PARTICIPANT_EMAIL']);
            $participant = $DB->getParticipant($participant_email);

            // submit a new participant
            if ($participant == Null) {
                $participant = new Participant(0, $DB);
                $participant->setEmail($participant_email);
                $participant->save();
            }

        // no valid email address
        } else {
            echo '<div class="message error">Die Emailadresse "' . $participant_email . '" ist nicht erlaubt!</div>';
        }
    }

    // try to find participation by existing participant
    if ($participant != Null && $participation == Null) {
        foreach ($raffle->getParticipations() as $p) {
            if ($p->getParticipant() == $participant) {
                $participation = $p;
                break;
            }
        }
    }

    // get participation by Id
    if ($participation == Null && isset($_REQUEST['PARTICIPATION_ID'])) {
        $id = intval($_REQUEST['PARTICIPATION_ID']);
        $participation = new Participation($id, $DB);
        if ($participation->getId() <= 0)
            $participation = Null;
        else
            $participant = $participation->getPArticipant();
    }

    // create new participation if not existent
    if ($participation == Null && $participant != Null && $raffle->getState() == Raffle::STATE_OPEN) {
        $participation = new Participation(0, $DB);
        $participation->setParticipant($participant);
        $participation->setRaffle($raffle);
        $participation->save();
    }

    // in regular case participant and participation have to be known now
    if ($participation != Null && $participation->getParticipant() != Null && $raffle->getState() == Raffle::STATE_OPEN) {

            // immediate entry if admin is logged in
            if (USER_IS_ADMIN) {
                $participation->setState(Participation::STATE_ENTRY_ACCEPTED);
                $participation->save();
                echo '<div class="message success">Teilnehmer ' . $participant->getEmail()  .  ' wurde eingetragen.</div>';

            // send email submission
            } else {

                // set request entry state and send email
                if ($participation->getState() == Participation::STATE_NOT_IN_DB or
                    $participation->getState() == Participation::STATE_ENTRY_REQUESTED or
                    $participation->getState() == Participation::STATE_DECLINE_ACCEPTED) {

                    $participation->setState(Participation::STATE_ENTRY_REQUESTED);
                    $newkey = $participation->createUserVerificationKey();
                    $participation->save();

                    if ($participation->sendNotification()) {
                        echo '<div class="message success">Eine Best&auml;tigungsanfrage wurde an &quot;' . $participant->getEmail()  .  '&quot; gesendet!</div>';
                    } else {
                        echo '<div class="message error">Es konnte keine Best&auml;tigungsanfrage an &quot;' . $participant->getEmail()  .  '&quot; gesendet werden!</div>';
                    }
                }
            }
    }
}



// ----------------------------------------------------------------------------
//                                    Sign Out
// ----------------------------------------------------------------------------

// signout from a participation
if ($raffle->getState()=="OPEN" && isset($_REQUEST['ACTION']) && $_REQUEST['ACTION']=="SIGNOUT") {

    // get requested participation id
    $participation_id = 0;
    if (isset($_REQUEST['PARTICIPATION_ID'])) $participation_id = intval($_REQUEST['PARTICIPATION_ID']);

    // get participation
    $participation = new Participation($participation_id, $DB);

    // invalid participation
    if ($participation->getId() == 0) {
        echo '<div class="message error">Ung&uuml;ltige Ziehung!</div>';

    // valid participation
    } else {

        // set user as forbidden if admin
        if (USER_IS_ADMIN) {
            $participation->setState(Participation::STATE_FORBIDDEN);
            $participation->save();
            echo '<div class="message success">Teilnehmer wurde ausgeschlossen.</div>';

        // sign out request
        } else if (in_array($participation->getState(), array(Participation::STATE_ENTRY_ACCEPTED, Participation::STATE_DECLINE_REQUESTED))) {

            // generate new sign out request
            $participation->setState(Participation::STATE_DECLINE_REQUESTED);
            $newkey = $participation->createUserVerificationKey();
            $participation->save();

            // send email notification
            if ($participation->sendNotification()) {
                echo '<div class="message success">Eine Best&auml;tigungsanfrage wurde an &quot;' . $participation->getParticipant()->getEmail()  .  '&quot; gesendet!</div>';
            } else {
                echo '<div class="message error">Es konnte keine Best&auml;tigungsanfrage an &quot;' . $participation->getParticipant()->getEmail()  .  '&quot; gesendet werden!</div>';
            }
        }

    }
}



// ----------------------------------------------------------------------------
//                                   Grant / Reject Win
// ----------------------------------------------------------------------------

// grant win
if (USER_IS_ADMIN && isset($_REQUEST['ACTION']) && $_REQUEST['ACTION']=="WIN_GRANT" && isset($_REQUEST['PARTICIPATION_ID'])) {

    // get participation
    $pn_id = intval($_REQUEST['PARTICIPATION_ID']);
    $pn = new Participation($pn_id, $DB);

    // set new state
    if ($pn->getState() == Participation::STATE_VOTED) {
        $pn->setState(Participation::STATE_WIN_GRANTED);
        $pn->save();
    }
}


// reject win
if (USER_IS_ADMIN && isset($_REQUEST['ACTION']) && $_REQUEST['ACTION']=="WIN_REJECT" && isset($_REQUEST['PARTICIPATION_ID'])) {

    // get participation
    $pn_id = intval($_REQUEST['PARTICIPATION_ID']);
    $pn = new Participation($pn_id, $DB);

    // set new state
    if ($pn->getState() == Participation::STATE_WIN_GRANTED) {
        $pn->setState(Participation::STATE_VOTED);
        $pn->save();
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

    <?php

        // participation sort function
        function cmp($pn1, $pn2) {
            global $raffle;
            if (in_array($raffle->getState(), array(Raffle::STATE_CLOSED, Raffle::STATE_INVALID)))
                return $pn1->getResultingScore() < $pn2->getResultingScore();
            else
                return $pn1->getId() > $pn2->getId();
        }

        // sort participations
        $sorted_participations = $raffle->getParticipations();
        usort($sorted_participations, "cmp");

        // iterate through participation
        foreach ($sorted_participations as $pn) :
    ?>

    <tr class="<?php echo $pn->getState() ?>">
            <td><?php echo $pn->getParticipant()->getEmail() ?></td>
            <td><?php echo $pn->getState() ?></td>
            <td><?php echo $pn->getResultingParticipations() ?></td>
            <td><?php echo $pn->getResultingWins() ?></td>
            <td><?php echo $pn->getResultingRandom() ?></td>
            <td><?php echo $pn->getResultingScore() ?></td>
            <?php

                if (USER_IS_ADMIN) {

                    // admin can directly allow or forbid user
                    if (in_array($pn->getState(), array(Participation::STATE_FORBIDDEN, Participation::STATE_ENTRY_REQUESTED, Participation::STATE_DECLINE_ACCEPTED))) {
                        echo '<td><a href="?ACTION=PARTICIPATE&PARTICIPATION_ID=' . $pn->getId() . '&RAFFLE_ID=' . $raffle->getId() . '"><img src="template/icon_user_allow.svg" width="16" title="Teilnehmer Erlauben"></a></td>';
                    } else if (in_array($pn->getState(), array(Participation::STATE_ENTRY_ACCEPTED, Participation::STATE_DECLINE_REQUESTED))) {
                        echo '<td><a href="?ACTION=SIGNOUT&PARTICIPATION_ID=' . $pn->getId() . '&RAFFLE_ID=' . $raffle->getId() . '"><img src="template/icon_user_forbid.svg" width="16" title="Teilnehmer Ausschlie&szlig;en"></a></td>';
                    }

                    // admin can grant and reject winners
                    if ($raffle->getState() == Raffle::STATE_CLOSED) {
                        if ($pn->getState() == Participation::STATE_VOTED) {
                            echo '<td><a href="?ACTION=WIN_GRANT&PARTICIPATION_ID=' . $pn->getId() . '&RAFFLE_ID=' . $raffle->getId() . '"><img src="template/icon_win_grant.svg" width="16" title="Teilnehmer als Gewinner setzen."></a></td>';
                        } else if ($pn->getState() == Participation::STATE_WIN_GRANTED) {
                            echo '<td><a href="?ACTION=WIN_REJECT&PARTICIPATION_ID=' . $pn->getId() . '&RAFFLE_ID=' . $raffle->getId() . '"><img src="template/icon_win_reject.svg" width="16" title="Gewinn entziehen."></a></td>';
                        }
                    }


                // user can sign in or sign out
                } else if ($raffle->getState() == Raffle::STATE_OPEN) {
                    if (in_array($pn->getState(), array(Participation::STATE_ENTRY_ACCEPTED, Participation::STATE_DECLINE_REQUESTED))) {
                        echo '<td><a href="?ACTION=SIGNOUT&PARTICIPATION_ID=' . $pn->getId() . '&RAFFLE_ID=' . $raffle->getId() . '"><img src="template/icon_user_forbid.svg" width="16" title="Austritt beantragen."></a></td>';
                    } else if (in_array($pn->getState(), array(Participation::STATE_DECLINE_ACCEPTED, Participation::STATE_ENTRY_REQUESTED))) {
                        echo '<td><a href="?ACTION=PARTICIPATE&PARTICIPATION_ID=' . $pn->getId() . '&RAFFLE_ID=' . $raffle->getId() . '"><img src="template/icon_user_allow.svg" width="16" title="Wiedereintrit beantragen."></a></td>';
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
