This project is hosted on <a href="https://github.com/Oinosseus/ticket-raffle">github</a><br>
Icons are used from <a href="https://openclipart.org">openclipart.org</a><br>
<br>
<br>

<?php

$license = file("LICENSE");

foreach($license as $line) {
    echo htmlentities($line) . "<br>";
}

?>
