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



    function selectTableRow($table, $id, $column_array) {
        // returns an accociative array

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



    function insertTableRow($table, $column_value_array) {
        // returns Id of newly inserted row

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



    function getRaffles() {
        // returns array of Raffle objects

        $ret = array();

        $query = "SELECT Id FROM Raffles";
        $results = $this->db->query($query);
        while ($row = $results->fetchArray()) {
            $ret[count($ret)] = new Raffle($row['Id'], $this);
        }

        return $ret;
    }

}

?>
