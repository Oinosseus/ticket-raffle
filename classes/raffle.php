<?php

//////////////////////////////////////////////////////////////////////////////
//                            Data Storage Class
//
// This class stores information about a raffle.

class Raffle {

    function __construct() {
        $this->Id          = 0;
        $this->Name        = "";
        $this->Winners     = 0;
        $this->OpenTime    = new DateTime();
        $this->CloseTime   = new DateTime();
        $this->DrawingTime = new DateTime();
        $this->State       = "";
    }

}

?>
