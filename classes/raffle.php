<?php


//////////////////////////////////////////////////////////////////////////////
//                            Data Storage Class
//
// This class stores information about a raffle.

class Raffle {

    // pseudo enums for state of a raffle
    const STATE_COMMITTED = 'COMMITTED';
    const STATE_OPEN      = 'OPEN';
    const STATE_CLOSED    = 'CLOSED';
    const STATE_INVALID   = 'INVALID';

    // constructor
    function __construct($id = 0, $db = Null) {

        // primary properties
        $this->_Db          = $db;
        $this->_Id          = intval($id);

        // default properties
        $this->_Name        = "";
        $this->_Winners     = 0;
        $this->_OpenTime    = new DateTime();
        $this->_OpenTime->setTimezone(new DateTimeZone(CONFIG_TIMEZONE));
        $this->_CloseTime   = new DateTime();
        $this->_CloseTime->setTimezone(new DateTimeZone(CONFIG_TIMEZONE));
        $this->_DrawingTime = Null;
        $this->_State       = Raffle::STATE_INVALID;

        // update from database
        if ($this->_Id > 0 && $this->_Db) $this->load();

    }


    // -----------------------------------------------------------------------
    //                            Getter / Setter
    // -----------------------------------------------------------------------


    function getId() {
        return $this->_Id;
    }


    function getName() {
        return $this->_Name;
    }

    function setName(string $name) {
        $this->_Name = "$name";
    }


    function getWinners() {
        return $this->_Winners;
    }

    function setWinners(int $winners) {
        $this->_Winners = intval($winners);
    }


    function getOpenTime() {
        return $this->_OpenTime;
    }

    function getOpenTimeHuman() {
        return $this->_OpenTime->format('Y-m-d H:i:s');
    }

    function setOpenTime($dateTime) {

        // get tim in string format
        $string_time = "";
        if (is_a($dateTime, "DateTime")) $string_time = $dateTime->format('Y-m-d H:i:s');
        else $string_time = "$dateTime";

        $this->_OpenTime = new DateTime($string_time, new DateTimeZone(CONFIG_TIMEZONE));
    }


    function getCloseTime() {
        return $this->_CloseTime;
    }

    function getCloseTimeHuman() {
        return $this->_CloseTime->format('Y-m-d H:i:s');
    }

    function setCloseTime($dateTime) {

        // get tim in string format
        $string_time = "";
        if (is_a($dateTime, "DateTime")) $string_time = $dateTime->format('Y-m-d H:i:s');
        else $string_time = "$dateTime";

        $this->_CloseTime = new DateTime($string_time, new DateTimeZone(CONFIG_TIMEZONE));
    }


    function getDrawingTime() {
        return $this->_DrawingTime;
    }

    function getDrawingTimeHuman() {
        if ($this->_DrawingTime)
            return $this->_DrawingTime->format('Y-m-d H:i:s');
        else
            return "";

    }

    function setDrawingTime($dateTime) {

        // get tim in string format
        $string_time = "";
        if (is_a($dateTime, "DateTime")) $string_time = $dateTime->format('Y-m-d H:i:s');
        else $string_time = "$dateTime";

        if ($dateTime == NULL || $string_time == "NULL" || $string_time == "") $this->_DrawingTime = NULL;
        else $this->_DrawingTime = new DateTime($string_time, new DateTimeZone(CONFIG_TIMEZONE));
    }


    function getState() {
        return $this->_State;
    }

    function setState(string $state) {
        if ($state == Raffle::STATE_COMMITTED or
            $state == Raffle::STATE_OPEN or
            $state == Raffle::STATE_CLOSED or
            $state == Raffle::STATE_INVALID ) {

            $this->_State = $state;
        }
    }



    // -----------------------------------------------------------------------
    //                            Methods
    // -----------------------------------------------------------------------

    // load raffle object properties from database
    function load() {

        // check possibility
        if ($this->_Id <= 0) return False;
        if (!$this->_Db)     return False;

        // access database
        $columns = ['Name', 'Winners', 'OpenTime' ,'CloseTime' ,'DrawingTime', 'State'];
        $db_fields = $this->_Db->selectTableRow("Raffles", $this->_Id, $columns);

        // set properties
        $this->setName($db_fields['Name']);
        $this->setWinners($db_fields['Winners']);
        $this->setOpenTime($db_fields['OpenTime']);
        $this->setCloseTime($db_fields['CloseTime']);
        $this->setDrawingTime($db_fields['DrawingTime']);
        $this->setState($db_fields['State']);
    }

    // save raffle object properties to database (update)
    function save() {

        // check possibility
        if (!$this->_Db)     return False;

        // create columns array
        $columns = array();
        $columns['Name']        = $this->_Name;
        $columns['Winners']     = intval($this->_Winners);
        $columns['OpenTime']    = $this->_OpenTime->format(DateTime::ATOM);
        $columns['CloseTime']   = $this->_CloseTime->format(DateTime::ATOM);
        if ($this->_DrawingTime) {
            $columns['DrawingTime'] = $this->_DrawingTime->format(DateTime::ATOM);
        } else {
            $columns['DrawingTime'] = "NULL";
        }
        $columns['State']       = $this->_State;

        // insert new database row
        if ($this->_Id <= 0) {
            $new_id = $this->_Db->insertTableRow("Raffles", $columns);
            $this->_Id = $new_id;

        // update existing database row
        } else {
            $this->_Db->updateTableRow("Raffles", $this->_Id, $columns);
        }
    }



}

?>
