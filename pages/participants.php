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
            <th>Next Weight</th>
        </tr>

    <?php
        foreach ($participants as $pt) {

            $participations = $pt->countParticipations();
            $wins           = $pt->countWins();
            $next_weight    = number_format( 100 * ($participations + 1 - $wins) / ($participations + 1));

            echo '<tr>';
            echo '    <td>' . $pt->getId() . '</td>';
            echo '    <td>' . $pt->getEmail() . '</td>';
            echo '    <td>' . $participations . '</td>';
            echo '    <td>' . $wins . '</td>';
            echo '    <td>' . $next_weight . '%</td>';
            echo '<tr>';
        }
    ?>

</table>
