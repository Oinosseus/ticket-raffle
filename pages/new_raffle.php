<?php
    // check for admin
    if (!USER_IS_ADMIN) {
        die("ERROR!: Admin rights required!");
    }

    // showing the new raffle submit form
    $show_new_raffle_form = true;

    // create raffle object
    $raffle = new Raffle(0, $DB);


    // save new event
    if (isset($_REQUEST['ACTION']) && $_REQUEST['ACTION']=="SAVE_NEW_RAFFLE") {

        $raffle->setName($_POST['RAFFLE_NAME']);
        $raffle->setWinners($_POST['RAFFLE_WINNERS']);
        $raffle->setOpenTime(new DateTime($_POST['RAFFLE_OPENTIME']));
        $raffle->setCloseTime(new DateTime($_POST['RAFFLE_CLOSETIME']));
        $raffle->setState(Raffle::STATE_COMMITTED);
        $raffle->save();

        if ($raffle->getId()) {
            echo '<div class="message success">Verlosung wurde mit der ID ' . $raffle->getId() . ' eingetragen!</div>';
            $show_new_raffle_form = false;
        } else {
            echo '<div class="message error">Verlosung konnte nicht in die Datenbank eingetragen werden!</div>';
        }
    }



?>


<?php if ($show_new_raffle_form === true): ?>
    <h1>Neue Verlosung Anlegen</h1>

    <form action="?ACTION=SAVE_NEW_RAFFLE" method="post">

        Name der Verlosung<br>
        <input type="text" name="RAFFLE_NAME" value="<?php echo $raffle->getName() ?>"><br>
        <br>

        Anzahl der Gewinner pro Ziehung<br>
        <input type="number" name="RAFFLE_WINNERS" min="1" value="<?php echo $raffle->getWinners() ?>"><br>
        <br>

        Zeitpunkt der Er&ouml;ffnung der Eintragungen (z.B. morgen: <?php echo $raffle->getOpenTimeHuman(); ?>)<br>
        <input type="datetime" name="RAFFLE_OPENTIME" value="<?php echo $raffle->getOpenTimeHuman(); ?>"><br>
        <br>

        Zeitpunkt der Verlosung (z.B. morgen: <?php echo $raffle->getCloseTimeHuman(); ?>)<br>
        <input type="datetime" name="RAFFLE_CLOSETIME" value="<?php echo $raffle->getCloseTimeHuman(); ?>"><br>
        <br>

        <button type="submit">Verlosung Eintragen</button>
    </form>
<?php endif; ?>
