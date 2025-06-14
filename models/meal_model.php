<?php
require_once __DIR__ . '/../config/database.php';

class MealModel {
    private $conn;

    public function __construct() {
        $this->conn = getDB();
    }

    public function getAllMeals() {
        try {
            // [UPDATED]: Using stored procedure to get all meals
            $stmt = $this->conn->prepare("CALL get_all_meals()");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error in getAllMeals: " . $e->getMessage());
            return [];
        }
    }

    public function getAvailableMeals() {
        try {
            // [UPDATED]: Using stored procedure to get available meals
            $stmt = $this->conn->prepare("CALL get_available_meals()");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error in getAvailableMeals: " . $e->getMessage());
            return [];
        }
    }

    public function getMealById($id) {
        try {
            // [UPDATED]: Using stored procedure to get meal by ID
            $stmt = $this->conn->prepare("CALL get_meal_by_id(?)");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error in getMealById: " . $e->getMessage());
            return false;
        }
    }

    public function addMeal($name, $description, $price) {
        try {
            // [UPDATED]: Using stored procedure to add meal
            $stmt = $this->conn->prepare("CALL add_meal(?, ?, ?)");
            return $stmt->execute([$name, $description, $price]);
        } catch(PDOException $e) {
            error_log("Error in addMeal: " . $e->getMessage());
            return false;
        }
    }

    public function updateMeal($id, $name, $description, $price, $available) {
        try {
            // [UPDATED]: Using stored procedure to update meal
            $stmt = $this->conn->prepare("CALL update_meal(?, ?, ?, ?, ?)");
            return $stmt->execute([$id, $name, $description, $price, $available]);
        } catch(PDOException $e) {
            error_log("Error in updateMeal: " . $e->getMessage());
            return false;
        }
    }

    public function deleteMeal($id) {
        try {
            // [UPDATED]: Using stored procedure to delete meal
            $stmt = $this->conn->prepare("CALL delete_meal(?)");
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            error_log("Error in deleteMeal: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalAvailableMeals() {
        try {
            // [UPDATED]: Using stored function to get total available meals
            $stmt = $this->conn->prepare("SELECT get_available_meals_count() AS total");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch(PDOException $e) {
            error_log("Error in getTotalAvailableMeals: " . $e->getMessage());
            return 0;
        }
    }
}
?>
