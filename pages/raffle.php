<?php

if (isset($_REQUEST['RAFFLE_ID'])) {

    $raffle = new Raffle(intval($_REQUEST['RAFFLE_ID']), $DB);
} else {
    $raffle = new Raffle();
}



// ----------------------------------------------------------------------------
//                        Submit New Participation
// ----------------------------------------------------------------------------

if ($raffle->getState()=="OPEN" && isset($_REQUEST['ACTION']) && $_REQUEST['ACTION']=="PARTICIPATE") {

    # get requested email address
    $participant_email = "";
    if (isset($_REQUEST['PARTICIPANT_EMAIL'])) {
        $participant_email = trim($_REQUEST['PARTICIPANT_EMAIL']);
    }

    # check for valid email address
    if (!preg_match(CONFIG_ALLOWEDEMAILREGEX, $participant_email)) {
        echo '<div class="message error">Die Emailadresse "' . $participant_email . '" ist nicht erlaubt!</div>';


    // email address is valid
    } else {

        // get participant
        $participant = Null;

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
        }

        // get drawing
        $drawing = Null;

        // try to find existing drawing
        foreach ($DB->getDrawings($raffle) as $d) {
            if ($d->getParticipant() == $participant) {
                $drawing = $d;
                break;
            }
        }

        // create new drawing
        if ($drawing == Null) {
            $drawing = new Drawing(0, $DB);
            $drawing->setRaffle($raffle);
            $drawing->setParticipant($participant);
        }

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
            <td><a href=""><img src="template/primary-delete.svg" width="16"></a></td>
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
