<?php
class Database {
    private $host = "localhost";
    private $db_name = "jeep_adventure";
    // Default credentials for local Laragon/MAMP/XAMPP development. Change as needed.
    private $username = "root";
    private $password = "";
    // Set to 'mysql' for MySQL/MariaDB or 'pgsql' for PostgreSQL. Adjust if you use a different driver.
    private $db_driver = "mysql";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Check available PDO drivers first to provide a clear error if the driver is missing.
            $availableDrivers = PDO::getAvailableDrivers();
            if (!in_array($this->db_driver, $availableDrivers)) {
                echo "Connection error: PDO driver '" . $this->db_driver . "' not found. Installed PDO drivers: " . implode(', ', $availableDrivers) . ". Please enable the driver in your php.ini (e.g. pdo_mysql or pdo_pgsql) and restart your web server.";
                return null;
            }

            if ($this->db_driver === 'mysql') {
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8";
            } else if ($this->db_driver === 'pgsql') {
                $dsn = "pgsql:host={$this->host};dbname={$this->db_name}";
            } else {
                echo "Connection error: Unsupported DB driver '{$this->db_driver}'.";
                return null;
            }

            $this->conn = new PDO($dsn, $this->username, $this->password);
            // Use exceptions for errors
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>