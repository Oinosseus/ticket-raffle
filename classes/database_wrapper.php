<?php

//////////////////////////////////////////////////////////////////////////////
//                            Database Wrapper
//
// This class wraps all database accesses.

class DatabaseWrapper {

    function __construct($db_filename) {

        $this->db = new SQLite3($db_filename);
    }

    function __destruct() {
        $this->db->close();
    }

    function checkStructure() {
        // creates tables and columns if not already existent

        // Events table
        $this->db->exec("CREATE TABLE IF NOT EXISTS Events ("
            . "Id INTEGER PRIMARY KEY AUTOINCREMENT,  "
            . "Name TEXT NOT NULL DEFAULT '', "
            . "Winners INTEGER NOT NULL DEFAULT 1, "
            . "OpenTime TEXT, "
            . "CloseTime TEXT, "
            . "DrawingTime TEXT, "
            . "State TEXT NOT NULL DEFAULT 'INVALID'"
            . ")");
    }

    function newEvent($name, $winners, $opentime, $closetime) {
    }

}

?>
