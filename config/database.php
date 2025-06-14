<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'mealprep';
    private $username = 'root';
    private $password = '';
    public $conn;

    // Update the getConnection method to ensure proper error handling and connection settings
    public function getConnection() {
        $this->conn = null;
        try {
            // Set proper charset in DSN
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", 
                $this->username, 
                $this->password
            );
            
            // Set error mode to exception for better error handling
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Set fetch mode to associative array by default
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Disable emulated prepared statements for better security
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            return $this->conn;
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            echo "Connection error: " . $exception->getMessage();
            return null;
        }
    }
}

// Helper function to get database connection
function getDB() {
    $database = new Database();
    $conn = $database->getConnection();
    if (!$conn) {
        error_log("Failed to get database connection");
    }
    return $conn;
}
?>
