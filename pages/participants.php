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

            echo '<tr>';
            echo '    <td>' . $pt->getId() . '</td>';
            echo '    <td>' . $pt->getEmail() . '</td>';
            echo '    <td>' . $pt->countParticipations() . '</td>';
            echo '    <td>' . $pt->countWins() . '</td>';
            echo '<tr>';
        }
    ?>

</table>
