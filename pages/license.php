This project is hosted on <a href="https://github.com/Oinosseus/ticket-raffle">github</a><br>
<br>
<br>

<?php

$license = file("LICENSE");

foreach($license as $line) {
    echo htmlentities($line) . "<br>";
}

?>
