<?php


//! This class stores information about a raffle.
class Raffle {

    // pseudo enums for state of a raffle
    const STATE_NOT_IN_DB = 'NOT_IN_DB';
    const STATE_COMMITTED = 'COMMITTED';
    const STATE_OPEN      = 'OPEN';
    const STATE_CLOSED    = 'CLOSED';
    const STATE_INVALID   = 'INVALID';

    /** Initializing the raffle object
     *
     * If id is known (> 0) and a reference to the database is given,
     * the constructer automatically calls load() to retrieve current data of the raffle.
     * @param $id int Optional database row Id (0 if not in database)
     * @param $db DatabaseWrapper A reference to the database wrapper.
     */
    function __construct($id = 0, DatabaseWrapper $db = Null) {

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
        $this->_State       = Raffle::STATE_NOT_IN_DB;

        // update from database
        if ($this->_Id > 0 && $this->_Db) $this->load();

    }


    // -----------------------------------------------------------------------
    //                            Getter / Setter
    // -----------------------------------------------------------------------
    //! @name Public Properties
    //! @{


    //! @return int Row-Id of the raffle in the database
    function getId() {
        return $this->_Id;
    }


    //! @return string The name oof the raffle
    function getName() {
        return $this->_Name;
    }

    //! @param $name string Set new name for the raffle.
    function setName($name) {
        $this->_Name = "$name";
    }


    //! @return int Amount of possible winners for the raffle
    function getWinners() {
        return $this->_Winners;
    }

    //! @param int Set new amount of possible winners for the raffle
    function setWinners($winners) {
        $this->_Winners = intval($winners);
    }


    //! @param DateTime The open time of the raffle
    function getOpenTime() {
        return $this->_OpenTime;
    }

    //! @param string The open time of the raffle in human readable format
    function getOpenTimeHuman() {
        return $this->_OpenTime->format('Y-m-d H:i:s');
    }

    //! @param $dateTime Setting a new open time for the raffle
    function setOpenTime($dateTime) {

        // get tim in string format
        $string_time = "";
        if (is_a($dateTime, "DateTime")) $string_time = $dateTime->format('Y-m-d H:i:s');
        else $string_time = "$dateTime";

        $this->_OpenTime = new DateTime($string_time, new DateTimeZone(CONFIG_TIMEZONE));
    }


    //! @return DateTime The close time for the raffle
    function getCloseTime() {
        return $this->_CloseTime;
    }

    //! @return string Thge close time of the raffle in human readable format
    function getCloseTimeHuman() {
        return $this->_CloseTime->format('Y-m-d H:i:s');
    }

    //! @param $dateTime DateTime Setting a new close time for the raffle.
    function setCloseTime($dateTime) {

        // get tim in string format
        $string_time = "";
        if (is_a($dateTime, "DateTime")) $string_time = $dateTime->format('Y-m-d H:i:s');
        else $string_time = "$dateTime";

        $this->_CloseTime = new DateTime($string_time, new DateTimeZone(CONFIG_TIMEZONE));
    }


    //! @return DateTime The drawing time of the raffle.
    function getDrawingTime() {
        return $this->_DrawingTime;
    }

    //! @return string The drawing time of the raffle in human readable format.
    function getDrawingTimeHuman() {
        if ($this->_DrawingTime)
            return $this->_DrawingTime->format('Y-m-d H:i:s');
        else
            return "";

    }

    /** Setting the drawing time of the raffle.
     * If the parameter is Null, the current time is used.
     * @param $dateTime|Null DateTime Setting a new drawing time of the raffle.
     */
    function setDrawingTime($dateTime = Null) {

        // set to now
        if ($dateTime == Null) {
        $dateTime = new DateTime();
        $dateTime->setTimezone(new DateTimeZone(CONFIG_TIMEZONE));
        }

        // get tim in string format
        $string_time = "";
        if (is_a($dateTime, "DateTime")) $string_time = $dateTime->format('Y-m-d H:i:s');
        else $string_time = "$dateTime";

        if ($dateTime == NULL || $string_time == "NULL" || $string_time == "") $this->_DrawingTime = NULL;
        else $this->_DrawingTime = new DateTime($string_time, new DateTimeZone(CONFIG_TIMEZONE));
    }

    //! @return string The current state of the raffle.
    function getState() {
        return $this->_State;
    }

    //! @param string setting a new state for the raffle.
    function setState($state) {
        if ($state == Raffle::STATE_COMMITTED or
            $state == Raffle::STATE_OPEN or
            $state == Raffle::STATE_CLOSED or
            $state == Raffle::STATE_INVALID ) {

            $this->_State = $state;
        }
    }

    // end of group public properties
    //! @}



    // -----------------------------------------------------------------------
    //                            Methods
    // -----------------------------------------------------------------------
    //! @name Public Methods
    // @{

    /** Load the current settings from the database.
     *
     *  If $db was not set in the __construct() this will ignore the load (no error is returned).
     *  If the database row id is unknown (not set in the constructor and raffle not saved) this will also do nothing.
     */
    function load() {

        // check possibility
        if ($this->_Id <= 0) return False;
        if (!$this->_Db)     return False;

        // access database
        $columns = array('Name', 'Winners', 'OpenTime' ,'CloseTime' ,'DrawingTime', 'State');
        $db_fields = $this->_Db->selectTableRow("Raffles", $this->_Id, $columns);

        // set properties
        $this->setName($db_fields['Name']);
        $this->setWinners($db_fields['Winners']);
        $this->setOpenTime($db_fields['OpenTime']);
        $this->setCloseTime($db_fields['CloseTime']);
        $this->setDrawingTime($db_fields['DrawingTime']);
        $this->setState($db_fields['State']);
    }

    /** Saving the actual values to the database.
     *
     *  If no $db was given at __construct() this operation will fail.
     *  If the database row id is already set (!=0) this operation will update the database row.
     *  If the database row id is not set (==0) this will create a new database row and update the id.
     *  @return bool True if raffle was saved successfully.
     */
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


    /** Get all participations for this raffle
     *
     * @return [Participation] An array of Participation objects
     */
    function getParticipations() {

        // get all participations from database where the raffle matches
        $ret = array();
        foreach ($this->_Db->findTableRows('Participations', array('Raffle' => $this->_Id)) as $id) {
            $ret[] = new Participation($id, $this->_Db);
        }

        return $ret;
    }

    // end group Methods
    //! q}



}

?>
