<?php

    if (isset($_REQUEST['RAFFLE_ID'])) {

        $raffle = new Raffle(intval($_REQUEST['RAFFLE_ID']), $DB);
    } else {
        $raffle = new Raffle();
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
        <td colspan="4">
            <form action="?ACTION=PARTICIPATE" method="post">
                <input type="email" name="PARTICIPANT_EMAIL"><button type="submit">Teilnehmen</button>
            </form>
        </td>
    </tr>
</table>
