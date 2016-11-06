<?php
    // check for admin
    if (!USER_IS_ADMIN) {
        die("ERROR!: Admin rights required!");
    }

    // default values
    $event_name = "";
    $event_winners = 1;
    $today = new DateTime();
    $today->setTimezone(new DateTimeZone(CONFIG_TIMEZONE));
    $event_opentime  = clone $today;
    $event_opentime->add(new DateInterval("P1D"));
    $event_closetime = clone $event_opentime;
    $event_closetime->add(new DateInterval("P7D"));


    // save new event
    if (isset($_REQUEST['ACTION']) && $_REQUEST['ACTION']=="SAVE_NEW_EVENT") {

        $event_name      = $_POST['EVENT_NAME'];
        $event_winners   = (int) $_POST['EVENT_WINNERS'];
        $event_opentime  = new DateTime($_POST['EVENT_OPENTIME']);
        $event_closetime = new DateTime($_POST['EVENT_CLOSETIME']);
    }



?>


<h1>Neues Event Anlegen</h1>

<form action="?ACTION=SAVE_NEW_EVENT" method="post">

    Name der Veranstaltung<br>
    <input type="text" name="EVENT_NAME" value="<?php echo $event_name ?>"><br>
    <br>

    Anzahl der Gewinner pro Ziehung<br>
    <input type="number" name="EVENT_WINNERS" min="1" value="<?php echo $event_winners ?>"><br>
    <br>

    Zeitpunkt der Er&ouml;ffnung der Eintragungen (z.B. morgen: <?php echo $event_opentime->format(DateTime::ATOM); ?>)<br>
    <input type="datetime" name="EVENT_OPENTIME" value="<?php echo $event_opentime->format(DateTime::ATOM); ?>"><br>
    <br>

    Zeitpunkt der Verlosung (z.B. morgen: <?php echo $event_closetime->format(DateTime::ATOM); ?>)<br>
    <input type="datetime" name="EVENT_CLOSETIME" value="<?php echo $event_closetime->format(DateTime::ATOM); ?>"><br>
    <br>

    <button type="submit">Veranstaltung Eintragen</button>
</form>
