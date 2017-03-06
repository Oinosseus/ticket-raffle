<?php

    function cmp($r1, $r2) {
        return $r2->getEmail() < $r1->getEmail();
    }

    $participants = $DB->getParticipants();
    usort($participants, "cmp");

?>

<table id="participants">
        <tr>
            <th>Id</th>
            <th>Email</th>
            <th>Particip.</th>
            <th>Wins</th>
        </tr>

    <?php
        foreach ($participants as $pt) {

            // count participations
            $previous_participations = 0;
            $previous_wins           = 0;
            foreach  ($DB->getParticipations() as $pn_previous) {
                // only count participations of the same participant
                if ($pn_previous->getParticipant() == $pt) {
                    // only count raffles that are in closed state
                    if ($pn_previous->getRaffle()->getState() == Raffle::STATE_CLOSED) {
                        // count when voted
                        if  ($pn_previous->getState() == Participation::STATE_VOTED) {
                            $previous_participations += 1;
                        // count when rejected
                        } else if ($pn_previous->getState() == Participation::STATE_WIN_REJECTED) {
                            $previous_participations += 1;
                        // count wind
                        } else if ($pn_previous->getState() == Participation::STATE_WIN_GRANTED) {
                            $previous_participations += 1;
                            $previous_wins += 1;
                        }
                    }
                }
            }

            echo '<tr>';
            echo '    <td>' . $pt->getId() . '</td>';
            echo '    <td>' . $pt->getEmail() . '</td>';
            echo '    <td>' . $previous_participations . '</td>';
            echo '    <td>' . $previous_wins . '</td>';
            echo '<tr>';
        }
    ?>

</table>
