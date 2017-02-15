<?php


//! This class stores information about a participant of a drawing.
class Participant {

    /** Initializing the participant object
     *
     * If id is known (> 0) and a reference to the database is given,
     * the constructer automatically calls load() to retrieve current data of the participant.
     * @param $id int Optional database row Id (0 if not in database)
     * @param $db DatabaseWrapper A reference to the database wrapper.
     */
    function __construct($id = 0, DatabaseWrapper $db = Null) {

        // primary properties
        $this->_Db          = $db;
        $this->_Id          = intval($id);

        // default properties
        $this->_Email       = "";

        // update from database
        if ($this->_Id > 0 && $this->_Db) $this->load();

    }


    // -----------------------------------------------------------------------
    //                            Getter / Setter
    // -----------------------------------------------------------------------
    //! @name Public Properties
    //! @{


    //! @return int Row-Id of the participant in the database
    function getId() {
        return $this->_Id;
    }


    //! @return string The email address of the participant
    function getEmail() {
        return $this->_Email;
    }

    //! @param $email string Set new email address of the participant
    function setEmail($email) {
        $this->_Email = trim("$email");
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
        $columns = ['Email'];
        $db_fields = $this->_Db->selectTableRow("Participants", $this->_Id, $columns);

        // set properties
        $this->setEmail($db_fields['Email']);
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
        $columns['Email'] = $this->_Email;

        // insert new database row
        if ($this->_Id <= 0) {
            $new_id = $this->_Db->insertTableRow("Participants", $columns);
            $this->_Id = $new_id;

        // update existing database row
        } else {
            $this->_Db->updateTableRow("Participants", $this->_Id, $columns);
        }
    }

    // end group Methods
    //! q}

}

?>
