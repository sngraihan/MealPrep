<?php
require_once __DIR__ . '/../config/database.php';

class OrderModel {
    private $conn;

    public function __construct() {
        $this->conn = getDB();
    }

    public function placeOrder($user_id, $order_date, $meal_id, $quantity) {
        try {
            // [UPDATED]: Using transaction and stored procedure for order placement
            $this->conn->beginTransaction();
            
            // Call the stored procedure
            $stmt = $this->conn->prepare("CALL place_order(?, ?, ?, ?)");
            $result = $stmt->execute([$user_id, $order_date, $meal_id, $quantity]);
            
            $this->conn->commit();
            return $result;
        } catch(PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error in placeOrder: " . $e->getMessage());
            return false;
        }
    }

    public function getUserOrders($user_id) {
        try {
            // [UPDATED]: Using stored procedure to get user orders
            $stmt = $this->conn->prepare("CALL get_user_orders(?)");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error in getUserOrders: " . $e->getMessage());
            return [];
        }
    }

    public function getAllOrders() {
        try {
            // [UPDATED]: Using stored procedure to get all orders with details
            $stmt = $this->conn->prepare("CALL get_all_orders()");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error in getAllOrders: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentOrders($limit = 5) {
        try {
            // [UPDATED]: Using stored procedure to get recent orders
            $stmt = $this->conn->prepare("CALL get_recent_orders(?)");
            $stmt->bindParam(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Recent orders query executed. Found " . count($results) . " results.");
            
            return $results;
        } catch(PDOException $e) {
            error_log("Error in getRecentOrders: " . $e->getMessage());
            return [];
        }
    }

    public function getTodayOrders() {
        try {
            // [UPDATED]: Using stored function to get today's orders count
            $stmt = $this->conn->prepare("SELECT get_today_orders_count() AS total");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch(PDOException $e) {
            error_log("Error in getTodayOrders: " . $e->getMessage());
            return 0;
        }
    }

    public function updateOrderStatus($order_id, $status) {
        try {
            // [UPDATED]: Using stored procedure to update order status
            $stmt = $this->conn->prepare("CALL update_order_status(?, ?)");
            return $stmt->execute([$order_id, $status]);
        } catch(PDOException $e) {
            error_log("Error in updateOrderStatus: " . $e->getMessage());
            return false;
        }
    }

    public function cancelOrder($order_id) {
        try {
            // [UPDATED]: Using stored procedure to cancel order
            $stmt = $this->conn->prepare("CALL cancel_order(?)");
            return $stmt->execute([$order_id]);
        } catch(PDOException $e) {
            error_log("Error in cancelOrder: " . $e->getMessage());
            return false;
        }
    }

    public function debugOrders() {
        try {
            // [UPDATED]: Using stored function to check database connection
            $stmt = $this->conn->prepare("SELECT count_all_orders() AS count");
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            return $count;
        } catch(PDOException $e) {
            error_log("Error in debugOrders: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }
}
?>
