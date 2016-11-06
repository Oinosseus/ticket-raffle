<?php

//////////////////////////////////////////////////////////////////////////////
//                            Database Wrapper
//
// This class wraps all database accesses.

class DatabaseWrapper {

    function __construct($db_filename) {

        $this->db = new SQLite3($db_filename, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

        if ($this->db->lastErrorCode()) {
            echo "[" . $this->db->lastErrorCode() . "] " . $this->db->lastErrorMsg() . "<br>";
        }
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

    function newEvent(string $name, int $winners, DateTime $opentime, DateTime $closetime) {

        // escape values
        $name = $this->db->escapeString($name);
        $winners = $this->db->escapeString($winners);
        $opentime = $this->db->escapeString($opentime->format(DateTime::ATOM));
        $closetime = $this->db->escapeString($closetime->format(DateTime::ATOM));

        // enter values
        $query = "INSERT INTO Events (Name, Winners, OpenTime, CloseTime, State) VALUES ('$name', '$winners', '$opentime', '$closetime', 'COMMITTED')";
        if (!$this->db->exec($query)) {
            echo "[" . $this->db->lastErrorCode() . "] " . $this->db->lastErrorMsg() . "<br>";
            return 0;
        }

        return $this->db->lastInsertRowID();

    }

}

?>
