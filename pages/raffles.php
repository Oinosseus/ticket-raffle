<?php

    // ------------------------------------------------------------------------
    //                          (In-) Validate Raffle
    // ------------------------------------------------------------------------

    if (USER_IS_ADMIN && isset($_REQUEST['RAFFLE_ID']) && isset($_REQUEST['ACTION']) && $_REQUEST['ACTION'] == 'IN-VALIDATE') {

        $raffle = new Raffle($_REQUEST['RAFFLE_ID'], $DB);

        if ($raffle->getState() == Raffle::STATE_CLOSED) {
            $raffle->setState(Raffle::STATE_INVALID);
            $raffle->save();
            echo '<div class="message success">Verlosung #' . $raffle->getId()  .  ' wurde invalidiert.</div>';

        } else if ($raffle->getState() == Raffle::STATE_INVALID) {
            $raffle->setState(Raffle::STATE_CLOSED);
            $raffle->save();
            echo '<div class="message success">Verlosung #' . $raffle->getId()  .  ' wurde revalidiert.</div>';
        }

    }



    // ------------------------------------------------------------------------
    //                        Sorted Raffle List
    // ------------------------------------------------------------------------

    function cmp($r1, $r2) {
        return $r2->getCloseTime() > $r1->getCloseTime();
    }

    $sorted_raffles = $DB->getRaffles();
    usort($sorted_raffles, "cmp");

?>

<table id="raffles">
        <tr>
            <th>Id</th>
            <th>Name</th>
            <th>OpenTime</th>
            <th>CloseTime</th>
            <th>DrawingTime</th>
            <th>State</th>
        </tr>

    <?php
        foreach ($sorted_raffles as $raffle) {
            echo '<tr class="' . $raffle->getState() . '">';
            echo '    <td>' . $raffle->getId() . '</td>';
            echo '    <td><a href="index.php?USER_PAGE=RAFFLE&RAFFLE_ID=' . $raffle->getId() . '">' . $raffle->getName() . '</a></td>';
            echo '    <td>' . $raffle->getOpenTimeHuman() . '</td>';
            echo '    <td>' . $raffle->getCloseTimeHuman() . '</td>';
            echo '    <td>' . $raffle->getDrawingTimeHuman() . '</td>';
            echo '    <td>' . $raffle->getState() . '</td>';

            // admin actions
            if (USER_IS_ADMIN) {
                echo '    <td>';

                // edit
                if ($raffle->getState() == Raffle::STATE_COMMITTED) {
                    echo '<a href="?USER_PAGE=EDIT_RAFFLE&RAFFLE_ID=' . $raffle->getId() . '" ><img src="template/icon_edit.svg" title="edit raffle" width="16"></a>';
                }

                // invalidate
                if ($raffle->getState() == Raffle::STATE_CLOSED) {
                    echo '<a href="?ACTION=IN-VALIDATE&RAFFLE_ID=' . $raffle->getId() . '" ><img src="template/icon_raffle_invalid.svg" title="invalidate raffle" width="16"></a>';
                }

                // validate
                if ($raffle->getState() == Raffle::STATE_INVALID) {
                    echo '<a href="?ACTION=IN-VALIDATE&RAFFLE_ID=' . $raffle->getId() . '" ><img src="template/icon_raffle_valid.svg" title="validate raffle" width="16"></a>';
                }

                echo '</td>';
            }

            echo '<tr>';
        }
    ?>

</table>
