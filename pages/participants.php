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
        </tr>

    <?php
        foreach ($participants as $p) {
            echo '<tr>';
            echo '    <td>' . $p->getId() . '</td>';
            echo '    <td>' . $p->getEmail() . '</td>';
            echo '<tr>';
        }
    ?>

</table>
