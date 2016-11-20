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

        // Raffles table
        $this->db->exec("CREATE TABLE IF NOT EXISTS Raffles ("
            . "Id INTEGER PRIMARY KEY AUTOINCREMENT,  "
            . "Name TEXT NOT NULL DEFAULT '', "
            . "Winners INTEGER NOT NULL DEFAULT 1, "
            . "OpenTime TEXT, "
            . "CloseTime TEXT, "
            . "DrawingTime TEXT, "
            . "State TEXT NOT NULL DEFAULT 'INVALID'"
            . ")");
    }

    function newRaffle(string $name, int $winners, DateTime $opentime, DateTime $closetime) {

        // escape values
        $name = $this->db->escapeString($name);
        $winners = $this->db->escapeString($winners);
        $opentime = $this->db->escapeString($opentime->format(DateTime::ATOM));
        $closetime = $this->db->escapeString($closetime->format(DateTime::ATOM));

        // enter values
        $query = "INSERT INTO Raffles (Name, Winners, OpenTime, CloseTime, DrawingTime, State) VALUES ('$name', '$winners', '$opentime', '$closetime', NULL, 'COMMITTED')";
        if (!$this->db->exec($query)) {
            echo "[" . $this->db->lastErrorCode() . "] " . $this->db->lastErrorMsg() . "<br>";
            return 0;
        }

        return $this->db->lastInsertRowID();

    }

    function getRaffles() {
        // returns array of Raffle objects

        $ret = array();

        $query = "SELECT Id, Name, Winners, OpenTime, CloseTime, DrawingTime, State FROM Raffles";
        $results = $this->db->query($query);
        while ($row = $results->fetchArray()) {
            $raffle = new Raffle();
            $raffle->Id          = $row['Id'];
            $raffle->Name        = $row['Name'];
            $raffle->Winners     = $row['Winners'];
            $raffle->OpenTime    = new DateTime($row['OpenTime'], new DateTimeZone(CONFIG_TIMEZONE));
            $raffle->CloseTime   = new DateTime($row['CloseTime'], new DateTimeZone(CONFIG_TIMEZONE));
            if ($row['DrawingTime'] == NULL) $raffle->DrawingTime = NULL;
            else $raffle->DrawingTime = new DateTime($row['DrawingTime'], new DateTimeZone(CONFIG_TIMEZONE));
            $raffle->State       = $row['State'];
            $ret[count($ret)] = $raffle;
        }

        return $ret;
    }

}

?>
