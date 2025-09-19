<?php
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $port = DB_PORT;
    private $charset = DB_CHARSET;
    private $conn = null;

    public function getConnection() {
        if ($this->conn == null) {
            try {
                $dsn = "mysql:host=" . $this->host .
                    ";port=" . $this->port .
                    ";dbname=" . $this->db_name .
                    ";charset=" . $this->charset;

                $this->conn = new PDO($dsn, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                throw new Exception("Error de conexión: " . $e->getMessage());
            }

        }
        return $this->conn;
    }

    public function closeConnection() {
        $this->conn = null;
    }
}
?>