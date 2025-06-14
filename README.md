# MealPrep #

MealPrep adalah sebuah sistem berbasis web yang memungkinkan pengguna untuk memesan makanan secara harian, khususnya bagi individu yang ingin menjaga pola makan sehat, diet, atau kebutuhan makan rutin tanpa harus repot memasak setiap hari. Sistem ini dibuat menggunakan PHP dan MySql juga dengan menerapkan poin penting dalam SQLnya yaitu stored procedure, trigger, transaction, dan stored function. Sistem ini juga sudah dilengkapi backup otomatis setiap harinya

![image](https://github.com/user-attachments/assets/6da284df-ee64-4e13-acd2-37b8aa091a3b)

## Detail Konsep ##

### Stored Procedure ###
![image](https://github.com/user-attachments/assets/9114c263-4e2f-4a4f-bf0c-e3a6242e17dc)

Ini adalah beberapa procedure penting yang digunakan

```models/order_model.php```

**Penggunaan**: Menggunakan stored procedure untuk mengambil semua data orders dengan join ke tabel users dan meals

```
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
```
**Penggunaan**: Menggunakan stored procedure untuk membuat orderan

```
    public function placeOrder($user_id, $order_date, $meal_id, $quantity) {
        try {
            // [UPDATED]: Better transaction handling and return value
            $this->conn->beginTransaction();
            
            // Call the stored procedure
            $stmt = $this->conn->prepare("CALL place_order(?, ?, ?, ?)");
            $result = $stmt->execute([$user_id, $order_date, $meal_id, $quantity]);
            
            if ($result) {
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollBack();
                return false;
            }
        } catch(PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error in placeOrder: " . $e->getMessage());
            return false;
        }
    }
```


### Triger ###


### Transaction ###


### Stored Function ###


### Backup Otomatis ###
