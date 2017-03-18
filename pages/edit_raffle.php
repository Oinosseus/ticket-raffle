<?php
    // check for admin
    if (!USER_IS_ADMIN) {
        die("ERROR!: Admin rights required!");
    }

    // some example dates
    $example_date_1week = new DateTime('tomorrow + 6days', new DateTimeZone(CONFIG_TIMEZONE));
    $example_date_2week = new DateTime('tomorrow + 13days', new DateTimeZone(CONFIG_TIMEZONE));

    // get raffle object
    if (isset($_REQUEST['RAFFLE_ID'])) {
        $raffle = new Raffle(intval($_REQUEST['RAFFLE_ID']), $DB);
    } else {
        $raffle = new Raffle(0, $DB);
        $raffle->setOpenTime($example_date_1week);
        $raffle->setCloseTime($example_date_2week);
    }


    // save new event
    if (!in_array($raffle->getState(), array(Raffle::STATE_NOT_IN_DB, Raffle::STATE_COMMITTED))) {
        echo '<div class="message error">Verlosung darf nicht mehr bearbeitet werden!</div>';

    } else if (isset($_REQUEST['ACTION']) && $_REQUEST['ACTION']=="SAVE_RAFFLE") {

        $raffle->setName($_POST['RAFFLE_NAME']);
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


<?php
if ($raffle->getId() == 0) {
    echo '<h1>Neue Verlosung Anlegen</h1>';
} else {
    echo '<h1>Verlosung Bearbeiten</h1>';
}
?>

<form action="?ACTION=SAVE_RAFFLE" method="post">

    <input type="hidden" name="RAFFLE_ID" value="<?php echo $raffle->getId() ?>">

    Name der Verlosung<br>
    <input type="text" name="RAFFLE_NAME" value="<?php echo $raffle->getName() ?>"><br>
    <br>

    Zeitpunkt der Er&ouml;ffnung der Eintragungen (z.B. in einer Woche: <?php echo $example_date_1week->format('Y-m-d H:i:s'); ?>)<br>
    <input type="datetime" name="RAFFLE_OPENTIME" value="<?php echo $raffle->getOpenTimeHuman(); ?>"><br>
    <br>

    Zeitpunkt der Verlosung (z.B. in zwei Wochen: <?php echo $example_date_2week->format('Y-m-d H:i:s'); ?>)<br>
    <input type="datetime" name="RAFFLE_CLOSETIME" value="<?php echo $raffle->getCloseTimeHuman(); ?>"><br>
    <br>

    <button type="submit">Verlosung Speichern</button>
</form>
