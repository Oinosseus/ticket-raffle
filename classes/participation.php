<?php


//! This class stores information about a participation.
class Participation {

    // pseudo enums for state of a Participation entry
    const STATE_NOT_IN_DB         = 'NOT_IN_DB';
    const STATE_ENTRY_REQUESTED   = 'ENTRY_REQUESTED';
    const STATE_ENTRY_ACCEPTED    = 'ENTRY_ACCEPTED';
    const STATE_DECLINE_REQUESTED = 'DECLINE_REQUESTED';
    const STATE_DECLINE_ACCEPTED  = 'DECLINE_ACCEPTED';
    const STATE_FORBIDDEN         = 'FORBIDDEN';
    const STATE_VOTED             = 'VOTED';
    const STATE_WIN_GRANTED       = 'WIN_GRANTED';



    /** Initializing the Participation object
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
        $this->_Raffle       = Null; // Raffle object
        $this->_Participant  = Null; // Participant object
        $this->_UserVerifKey = "";
        $this->_State        = Participation::STATE_NOT_IN_DB;
        $this->_ResultingParticipations = 0;
        $this->_ResultingWins           = 0;
        $this->_ResultingRandom         = 0;
        $this->_ResultingScore          = 0;

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

    //! @return Raffle|Null the corresponding raffle for this Participation
    function getRaffle() {
        return $this->_Raffle;
    }

    //! @param Raffle Setting the raffle of this Participation
    // @warning This must be made with care! Can only be set if not set before.
    function setRaffle(Raffle $raffle) {
        if ($this->_Raffle == Null) {
            $this->_Raffle = $raffle;
        }
    }

    //! @return Participant|Null The Participant of this Participation
    function getParticipant() {
        return $this->_Participant;
    }

    //! @param $participant Participant Setting the Participant of this Participation.
    //! @warning This must be made with care! Can only be set if not set before.
    function setParticipant(Participant $participant) {
        if ($this->_Participant == Null) {
            $this->_Participant = $participant;
        }
    }

    //! @return string The current key for user action verification.
    function getUserVerificationKey() {
        return $this->_UserVerifKey;
    }

    //! @return string The current state of the Participation
    function getState() {
        return $this->_State;
    }

    //! @param state string The new state for the Participation
    function setState($state) {
        if ($state == Participation::STATE_ENTRY_REQUESTED or
            $state == Participation::STATE_ENTRY_ACCEPTED or
            $state == Participation::STATE_DECLINE_REQUESTED or
            $state == Participation::STATE_DECLINE_ACCEPTED or
            $state == Participation::STATE_FORBIDDEN or
            $state == Participation::STATE_VOTED or
            $state == Participation::STATE_WIN_GRANTED or
            $state == Participation::STATE_WIN_REJECTED ) {

            $this->_State = $state;
        }
    }

    //! @return int The number of all participations of a Participant that were determined at a Participation
    function getResultingParticipations () {
        return $this->_ResultingParticipations;
    }

    //! @return int The number of wins that were determined at a drawing
    function getResultingWins () {
        return $this->_ResultingWins;
    }

    //! @return int The random number that was diced at a drawing
    function getResultingRandom () {
        return $this->_ResultingRandom;
    }

    //! @return int The final score of a drawing
    function getResultingScore () {
        return $this->_ResultingScore;
    }


    // end of group public properties
    //! @}



    // -----------------------------------------------------------------------
    //                            Methods
    // -----------------------------------------------------------------------
    //! @name Public Methods
    // @{


    /** Generate a new user verification key.
     *
     * This is used to identify a user.
     *
     * @return string The newly generated key.
     */
    function createUserVerificationKey() {
        $this->_UserVerifKey = uniqid();
        return $this->_UserVerifKey;
    }


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
        $columns = array('Raffle', 'Participant', 'State', 'UserVerificationKey', 'ResultingParticipations', 'ResultingWins', 'ResultingRandom', 'ResultingScore');
        $db_fields = $this->_Db->selectTableRow("Participations", $this->_Id, $columns);

        // set properties
        $this->setRaffle(new Raffle($db_fields['Raffle'], $this->_Db));
        $this->setParticipant(new Participant($db_fields['Participant'], $this->_Db));
        $this->_UserVerifKey            = $db_fields['UserVerificationKey'];
        $this->setState($db_fields['State']);
        $this->_ResultingParticipations = intval($db_fields['ResultingParticipations']);
        $this->_ResultingWins           = intval($db_fields['ResultingWins']);
        $this->_ResultingRandom         = intval($db_fields['ResultingRandom']);
        $this->_ResultingScore          = intval($db_fields['ResultingScore']);
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
        if ($this->_Raffle->getId() <= 0) return False;
        if ($this->_Participant->getId()  <= 0) return False;

        // create columns array
        $columns = array();
        $columns['Raffle'] = $this->_Raffle->getId();
        $columns['Participant'] = $this->_Participant->getId();
        $columns['UserVerificationKey'] = $this->_UserVerifKey;
        $columns['State'] = $this->_State;
        $columns['ResultingParticipations'] = $this->_ResultingParticipations;
        $columns['ResultingWins'] = $this->_ResultingWins;
        $columns['ResultingRandom'] = $this->_ResultingRandom;
        $columns['ResultingScore'] = $this->_ResultingScore;

        // insert new database row
        if ($this->_Id <= 0) {
            $new_id = $this->_Db->insertTableRow("Participations", $columns);
            $this->_Id = $new_id;

        // update existing database row
        } else {
            $this->_Db->updateTableRow("Participations", $this->_Id, $columns);
        }
    }


    /** Sending email notification to Participant.
     *
     * The notification content depends of the current state.
     * It is recommended to call createUserVerificationKey() and save() before calling this function.
     *
     * @return bool True if notification could be sent.
     */
    function sendNotification() {

        // get email submit url
        $submit_url  = "";
        $submit_url .= $_SERVER["REQUEST_SCHEME"] . "://";
        $submit_url .= $_SERVER["HTTP_HOST"];
        $submit_url .= $_SERVER["PHP_SELF"];
        $submit_url .= "?USER_PAGE=EMAIL_SUBMISSION";
        $submit_url .= "&UserVerificationKey=" . $this->_UserVerifKey;
        $submit_url .= "&ParticipationId=" . $this->_Id;

        // get url with link to raffle
        $raffle_url = "";
        $raffle_url .= $_SERVER["REQUEST_SCHEME"] . "://";
        $raffle_url .= $_SERVER["HTTP_HOST"];
        $raffle_url .= $_SERVER["PHP_SELF"];
        $raffle_url .= "?USER_PAGE=RAFFLE";
        $raffle_url .= "&RAFFLE_ID=" . $this->_Raffle->getId();

        // mail parameters
        $mail_to       = $this->_Participant->getEmail();
        $mail_subject  = "Ticket Raffle";
        $mail_header   = "MIME-Version: 1.0\n";
        $mail_header  .= "Content-type: text/html; charset=iso-8859-1\n";
        $mail_header  .= "X-Mailer: PHP ". phpversion() . "\n";

        // send entry request message
        if ($this->_State == Participation::STATE_ENTRY_REQUESTED) {
            $mail_message  = "<html><body>";
            $mail_message .= "Hallo,<br><br>";
            $mail_message .= "Sie wurden f&uuml;r die Verlosung \"" . $this->_Raffle->getName() . "\" eingetragen.<br>";
            $mail_message .= "Die Ziehung findet am " . $this->_Raffle->getCloseTimeHuman() . " statt.<br><br>";
            $mail_message .= "<a href=\"" . $submit_url . "\">";
            $mail_message .= "Bitte klicken Sie diesen Link um die Teilnahme zu best&auml;tigen.</a><br><br>";
            $mail_message .= "MfG<br>";
            $mail_message .= "</body></html>";

        // send decline request message
        } else if ($this->_State == Participation::STATE_DECLINE_REQUESTED) {
            $mail_message  = "<html><body>";
            $mail_message .= "Hallo,<br><br>";
            $mail_message .= "Sie haben beantragt an der Verlosung \"" . $this->_Raffle->getName() . "\" nicht mehr teilzunehmen.<br>";
            $mail_message .= "<a href=\"" . $submit_url . "\">";
            $mail_message .= "Bitte klicken Sie diesen Link um den Ausschluss zu best&auml;tigen.</a><br><br>";
            $mail_message .= "MfG<br>";
            $mail_message .= "</body></html>";

        // send win notification
        } else if ($this->_State == Participation::STATE_WIN_GRANTED) {
            $mail_message  = "<html><body>";
            $mail_message .= "Herzlichen Gl&uuml;ckwunsch,<br><br>";
            $mail_message .= "sie haben bei der Verlosung \"<a href=\"" . $raffle_url . "\">" . $this->_Raffle->getName() . "</a>\" gewonnen.<br>";
            $mail_message .= "<br>MfG<br>";
            $mail_message .= "</body></html>";

        // send not-win notification
        } else if ($this->_State == Participation::STATE_VOTED) {
            $mail_message  = "<html><body>";
            $mail_message .= "Hallo,<br><br>";
            $mail_message .= "die Verlosung \"<a href=\"" . $raffle_url . "\">" . $this->_Raffle->getName() . "</a>\" wurde druchgef&uuml;hrt.<br>";
            $mail_message .= "Leider haben Sie diesmal nicht gewonnen.<br>";
            $mail_message .= "<br>MfG<br>";
            $mail_message .= "</body></html>";

        // state does not allow to send a message
        } else {
            return False;
        }

        return mail($mail_to, $mail_subject, $mail_message, $mail_header);
    }


    /** Calculates a weighted random score.
     * This function only operates if the current state is ENTRY_ACCEPTED or DECLINE_REQUESTED.
     * Afterwards the state changes to VOTED.
     * The vote is not saved automatically (call save() afterwards).
     * @return bool True if voted, else False
     */
    function vote() {

        // only operate in correct state
        if (!in_array($this->_State, array(Participation::STATE_ENTRY_ACCEPTED, Participation::STATE_DECLINE_REQUESTED))) return False;

        // only if participant is valid
        if ($this->_Participant == Null) return False;

        // calculate scores
        $this->_ResultingParticipations = $this->_Participant->countParticipations() + 1;
        $this->_ResultingWins           = $this->_Participant->countWins();
        $this->_ResultingRandom         = rand(0,10000);
        $this->_ResultingScore          = $this->_ResultingRandom * ($this->_ResultingParticipations - $this->_ResultingWins) / $this->_ResultingParticipations;
        $this->_State                   = Participation::STATE_VOTED;

        return True;
    }


    // end group Methods
    //! q}

}

?>
