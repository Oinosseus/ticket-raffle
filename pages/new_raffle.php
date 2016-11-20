<?php
    // check for admin
    if (!USER_IS_ADMIN) {
        die("ERROR!: Admin rights required!");
    }

    // showing the new raffle submit form
    $show_new_raffle_form = true;

    // default values
    $raffle_name = "";
    $raffle_winners = 1;
    $today = new DateTime();
    $today->setTimezone(new DateTimeZone(CONFIG_TIMEZONE));
    $raffle_opentime  = clone $today;
    $raffle_opentime->add(new DateInterval("P1D"));
    $raffle_closetime = clone $raffle_opentime;
    $raffle_closetime->add(new DateInterval("P7D"));


    // save new event
    if (isset($_REQUEST['ACTION']) && $_REQUEST['ACTION']=="SAVE_NEW_RAFFLE") {

        $raffle_name      = $_POST['RAFFLE_NAME'];
        $raffle_winners   = (int) $_POST['RAFFLE_WINNERS'];
        $raffle_opentime  = new DateTime($_POST['RAFFLE_OPENTIME']);
        $raffle_closetime = new DateTime($_POST['RAFFLE_CLOSETIME']);

        $id = $DB->newRaffle($raffle_name, $raffle_winners, $raffle_opentime, $raffle_closetime);

        if ($id) {
            echo '<div class="message success">Verlosung wurde mit der ID '.$id.' eingetragen!</div>';
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
        <input type="text" name="RAFFLE_NAME" value="<?php echo $raffle_name ?>"><br>
        <br>

        Anzahl der Gewinner pro Ziehung<br>
        <input type="number" name="RAFFLE_WINNERS" min="1" value="<?php echo $raffle_winners ?>"><br>
        <br>

        Zeitpunkt der Er&ouml;ffnung der Eintragungen (z.B. morgen: <?php echo $raffle_opentime->format(DateTime::ATOM); ?>)<br>
        <input type="datetime" name="RAFFLE_OPENTIME" value="<?php echo $raffle_opentime->format(DateTime::ATOM); ?>"><br>
        <br>

        Zeitpunkt der Verlosung (z.B. morgen: <?php echo $raffle_closetime->format(DateTime::ATOM); ?>)<br>
        <input type="datetime" name="RAFFLE_CLOSETIME" value="<?php echo $raffle_closetime->format(DateTime::ATOM); ?>"><br>
        <br>

        <button type="submit">Verlosung Eintragen</button>
    </form>
<?php endif; ?>
