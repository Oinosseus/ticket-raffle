<?php

$license = file("LICENSE");

foreach($license as $line) {
    echo htmlentities($line) . "<br>";
}

?>
