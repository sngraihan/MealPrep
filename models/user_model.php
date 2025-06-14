<?php
require_once __DIR__ . '/../config/database.php';

class UserModel {
    private $conn;

    public function __construct() {
        $this->conn = getDB();
    }

    public function register($username, $email, $password) {
        try {
            // [UPDATED]: Using transaction and stored procedure for user registration
            $this->conn->beginTransaction();
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("CALL register_user(?, ?, ?, 'user')");
            $result = $stmt->execute([$username, $email, $hashed_password]);
            
            $this->conn->commit();
            return $result;
        } catch(PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error in register: " . $e->getMessage());
            return false;
        }
    }

    public function login($username, $password) {
        try {
            // [UPDATED]: Using stored procedure to get user for login
            $stmt = $this->conn->prepare("CALL get_user_by_username_or_email(?)");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Error in login: " . $e->getMessage());
            return false;
        }
    }

    public function getUserById($id) {
        try {
            // [UPDATED]: Using stored procedure to get user by ID
            $stmt = $this->conn->prepare("CALL get_user_by_id(?)");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error in getUserById: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalUsers() {
        try {
            // [UPDATED]: Using stored function to get total users
            $stmt = $this->conn->prepare("SELECT get_total_users() AS total");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch(PDOException $e) {
            error_log("Error in getTotalUsers: " . $e->getMessage());
            return 0;
        }
    }

    public function emailExists($email) {
        try {
            // [UPDATED]: Using stored function to check if email exists
            $stmt = $this->conn->prepare("SELECT check_email_exists(?) AS exists_flag");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC)['exists_flag'] == 1;
        } catch(PDOException $e) {
            error_log("Error in emailExists: " . $e->getMessage());
            return false;
        }
    }

    public function usernameExists($username) {
        try {
            // [UPDATED]: Using stored function to check if username exists
            $stmt = $this->conn->prepare("SELECT check_username_exists(?) AS exists_flag");
            $stmt->execute([$username]);
            return $stmt->fetch(PDO::FETCH_ASSOC)['exists_flag'] == 1;
        } catch(PDOException $e) {
            error_log("Error in usernameExists: " . $e->getMessage());
            return false;
        }
    }

    public function isAdmin($user_id) {
        try {
            // [UPDATED]: Using stored function to check if user is admin
            $stmt = $this->conn->prepare("SELECT is_admin(?) AS admin_flag");
            $stmt->execute([$user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC)['admin_flag'] == 1;
        } catch(PDOException $e) {
            error_log("Error in isAdmin: " . $e->getMessage());
            return false;
        }
    }
}
?>
