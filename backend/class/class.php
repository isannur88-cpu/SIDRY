<?php

class sidry {

    // Connection
    private $conn;

    // Table
    private $db_table = "station";

    // Columns
    public $id;
    public $suhu;
    public $kelembaban;
    public $rain;
    public $status_hujan;
    public $created_at;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE
    public function createLogData() {

        $sqlQuery = "INSERT INTO {$this->db_table}
                    (suhu, kelembaban, rain, status_hujan)
                    VALUES
                    (:suhu, :kelembaban, :rain, :status_hujan)";

        $stmt = $this->conn->prepare($sqlQuery);

        $this->suhu = trim($this->suhu);
        $this->kelembaban = trim($this->kelembaban);
        $this->rain = trim($this->rain);
        $this->status_hujan = trim($this->status_hujan);

        $stmt->bindParam(':suhu', $this->suhu);
        $stmt->bindParam(':kelembaban', $this->kelembaban);
        $stmt->bindParam(':rain', $this->rain);
        $stmt->bindParam(':status_hujan', $this->status_hujan);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
