<?php
class Database {
    private $host = "crmregistroventas.online";
    private $db_name = "punto_venta";
    private $username = "noreplyc_alexsanchez09";
    private $password = "SWn_VrAc3K7lq;LS";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>