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

    } else {


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


        if ($participant->getId() > 0)
            echo '<div class="message success">Die Emailadresse "' . $participant->getEmail() . '" wurde eingetragen!</div>';
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

<table id="raffle_participants">
    <tr>
        <th>Email</th>
        <th>State</th>
        <th title="Participations">P</th>
        <th title="Wins">W</th>
    </tr>
    <tr>
        <?php if ($raffle->getState() === "OPEN") : ?>
        <td colspan="4">
            <form action="?ACTION=PARTICIPATE" method="post">
                <input type="hidden" name="RAFFLE_ID" value="<?php echo $raffle->getId() ?>" />
                <input type="text" name="PARTICIPANT_EMAIL"><button type="submit">Teilnehmen</button>
            </form>
        </td>
        <?php endif; ?>
    </tr>
</table>
