<?php

//! Database Wrapper
// This class wraps all database accesses.
class DatabaseWrapper {


    //! @param $db_filename The path to the SQLite3 database file.
    function __construct($db_filename) {

        // check if database file already exists
        $database_exist = is_file($db_filename);

        // open database
        $this->db = new SQLite3($db_filename, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        if ($this->db->lastErrorCode()) {
            echo "[" . $this->db->lastErrorCode() . "] " . $this->db->lastErrorMsg() . "<br>";
        }

        // create tables if database did not exist before
        if (!$database_exist) {
            $this->checkStructure();
        }
    }



    function __destruct() {
        $this->db->close();
    }


    // ------------------------------------------------------------------------
    //                             Public Methods
    // ------------------------------------------------------------------------

    //! @name Public Methods
    // @{



    //! Creates tables and columns if not already existent.
    function checkStructure() {

        // Raffles table
        $this->db->exec("CREATE TABLE IF NOT EXISTS Raffles ("
            . "Id INTEGER PRIMARY KEY AUTOINCREMENT,  "
            . "Name TEXT NOT NULL DEFAULT '', "
            . "Winners INTEGER NOT NULL DEFAULT 1, "
            . "OpenTime TEXT, "
            . "CloseTime TEXT, "
            . "DrawingTime TEXT, "
            . "State TEXT NOT NULL DEFAULT '" . Raffle::STATE_INVALID . "'"
            . ")");

        // Participants table
        $this->db->exec("CREATE TABLE IF NOT EXISTS Participants ("
            . "Id INTEGER PRIMARY KEY AUTOINCREMENT,  "
            . "Email TEXT NOT NULL DEFAULT ''"
            . ")");

        // Participations table
        $this->db->exec("CREATE TABLE IF NOT EXISTS Participations ("
            . "Id INTEGER PRIMARY KEY AUTOINCREMENT,  "
            . "Raffle INTEGER NOT NULL DEFAULT 1, "
            . "Participant INTEGER NOT NULL DEFAULT 1, "
            . "UserVerificationKey TEXT NOT NULL DEFAULT '',"
            . "State TEXT NOT NULL DEFAULT '" . Drawing::STATE_FORBIDDEN . "',"
            . "ResultingParticipations INTEGER,"
            . "ResultingWins INTEGER,"
            . "ResultingRandom INTEGER,"
            . "ResultingScore INTEGER"
            . ")");

    }



    //! A database request for a certain row.
    // @return [string -> string] associative array
    // @param $tbale string Name of the table
    // @param $id int The row id number.
    // @param $column_array [string] An array of column names that are in the keys in the returned array.
    function selectTableRow($table, $id, $column_array) {

        // escape values
        $table = $this->db->escapeString($table);
        $id    = $this->db->escapeString(intval($id));
        $column_string = "";
        foreach ($column_array as $colname) {
            if (strlen($column_string) > 0) $column_string .= ", ";
            $column_string .= $this->db->escapeString($colname);
        }

        // db request
        $query = "SELECT $column_string FROM $table WHERE Id = '$id'";
        $results = $this->db->query($query);

        // return result
        return $results->fetchArray();
    }



    //! Queries the database for all rows that matches the search values.
    // @return [string -> string] associative array
    // @param $tbale string Name of the table
    // @param $colum_requests [sring] Requested column names that shall be in the return array
    // @param $column_search_array [string -> string] Associative array where column names are mapped to their requested content values
    function findTableRows($table, $colum_requests, $column_search_array) {

        // escape table
        $table = $this->db->escapeString($table);

        // column name string
        $column_string = "";
        foreach ($colum_requests as $column) {
            if (strlen($column_string) > 0) $column_string .= ", ";
            $column_string .= $this->db->escapeString($column);
        }

        // where clause string
        $where_string = "";
        $array_keys = array_keys($column_search_array);
        foreach ($column_search_array as $key => $value) {
             if (strlen($where_string) > 0) $where_string .= " AND ";
            $where_string .= $this->db->escapeString($key) . "='" . $this->db->escapeString($value) ."'";
        }

        // check for valid inputs
        if (strlen($column_string) == 0 || strlen($where_string) == 0)
            return array();

        // db request
        $query = "SELECT $column_string FROM $table WHERE $where_string";
        $results = $this->db->query($query);

        // return result
        return $results->fetchArray();
    }



    //! An update request for an existing row.
    // @param $tbale string Name of the table
    // @param $id int The row id number.
    // @param $column_value_array [string -> string] An associative array where the keys are table columns and the values are the updated column values.
    function updateTableRow($table, $id, $column_value_array) {

        // escape values
        $table = $this->db->escapeString($table);
        $id    = $this->db->escapeString(intval($id));
        $column_string = "";
        $values_string = "";
        foreach (array_keys($column_value_array) as $colname) {
            if (strlen($column_string) > 0) {
                $column_string .= ", ";
                $values_string .= ", ";
            }
            $column_string .= $this->db->escapeString($colname);
            $values_string .= "'" . $this->db->escapeString($column_value_array[$colname]) . "'";
        }

        // db request
        $query = "UPDATE $table SET ($column_string) = ($values_string) WHERE Id = '$id'";
        if (!$this->db->exec($query)) {
            echo "[" . $this->db->lastErrorCode() . "] " . $this->db->lastErrorMsg() . "<br>";
        }
    }



    //! Adding a row into a table.
    // @param $tbale string Name of the table
    // @param $column_value_array [string -> string] An associative array where the keys are table columns and the values are the updated column values.
    // @return int The row id of the newly added table row.
    function insertTableRow($table, $column_value_array) {

        // escape values
        $table = $this->db->escapeString($table);
        $column_string = "";
        $values_string = "";
        foreach (array_keys($column_value_array) as $colname) {
            if (strlen($column_string) > 0) {
                $column_string .= ", ";
                $values_string .= ", ";
            }
            $column_string .= $this->db->escapeString($colname);
            $values_string .= "'" . $this->db->escapeString($column_value_array[$colname]) . "'";
        }

        // db request
        $query = "INSERT INTO $table ($column_string) VALUES ($values_string)";
        if (!$this->db->exec($query)) {
            echo "[" . $this->db->lastErrorCode() . "] " . $this->db->lastErrorMsg() . "<br>";
            return 0;
        }

        return $this->db->lastInsertRowID();
    }



    //! Request all existing raffles from the database.
    // @return [Raffle] An array of Raffle objects
    function getRaffles() {

        $ret = array();

        $query = "SELECT Id FROM Raffles";
        $results = $this->db->query($query);
        while ($row = $results->fetchArray()) {
            $ret[count($ret)] = new Raffle($row['Id'], $this);
        }

        return $ret;
    }



    //! Request all existing participants from the database
    // @return [Participant] An array of Participant objects
    function getParticipants() {
        $ret = array();

        $query = "SELECT Id FROM Participants";
        $results = $this->db->query($query);
        while ($row = $results->fetchArray()) {
            $ret[count($ret)] = new Participant($row['Id'], $this);
        }

        return $ret;
    }


    //! Get a Participant object identified by email.
    // This functions returns the first Participant in the database with matching email.
    // If no matching email excist, Null is returned.
    // @return Participant|Null The requested Participant or Null.
    function getParticipant($email) {

        $email = $this->db->escapeString($email);

        $query = "SELECT Id FROM Participants WHERE Email = '$email'";
        $results = $this->db->query($query);
        while ($row = $results->fetchArray()) {
            return new Participant($row['Id'], $this);
        }

        return Null;
    }


    //! Request all existing participations from the database
    // @param $raffle Raffle|Null If set, only the participation for a certain raffle are returned.
    // @return [Participation] An array of Participation objects
    function getParticipations($raffle = Null) {
        $ret = array();

        # setup the query
        if ($raffle == Null)
            $query = "SELECT Id FROM Participations";
        else
            $query = "SELECT Id FROM Participations WHERE Raffle = '" . $raffle->getId() . "'";

        # db request
        $results = $this->db->query($query);
        while ($row = $results->fetchArray()) {
            $ret[count($ret)] = new Participation($row['Id'], $this);
        }

        return $ret;
    }

    // end of group methods
    //! @}

}

?>
