<?php

    function cmp($r1, $r2) {
        return $r2->CloseTime->getTimeStamp() - $r1->CloseTime->getTimeStamp();
    }

    $sorted_raffles = $DB->getRaffles();
    usort($sorted_raffles, "cmp");


?>

<table id="raffles">
        <tr>
            <th>Id</th>
            <th>Name</th>
            <th>Winners</th>
            <th>OpenTime</th>
            <th>CloseTime</th>
            <th>DrawingTime</th>
            <th>State</th>
        </tr>

    <?php
        foreach ($sorted_raffles as $raffle) {
            echo '<tr>';
            echo '    <td>' . $raffle->Id . '</td>';
            echo '    <td>' . $raffle->Name . '</td>';
            echo '    <td>' . $raffle->Winners . '</td>';
            echo '    <td>' . $raffle->OpenTime->format('Y-m-d H:i:s') . '</td>';
            echo '    <td>' . $raffle->CloseTime->format('Y-m-d H:i:s') . '</td>';
            if ($raffle->DrawingTime)
                echo '    <td>' . $raffle->DrawingTime->format('Y-m-d H:i:s') . '</td>';
            else
                echo '    <td></td>';
            echo '    <td>' . $raffle->State . '</td>';
            echo '<tr>';
        }
    ?>

</table>
