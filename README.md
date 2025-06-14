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

![image](https://github.com/user-attachments/assets/a1a3e604-fa25-40ed-bf4b-e6281986ffae)

Ini adalah beberapa triggers penting yang digunakan

```
CREATE TRIGGER trg_update_order_total //
    AFTER INSERT ON order_items
    FOR EACH ROW
BEGIN
    UPDATE orders 
    SET total_price = calculate_order_total(NEW.order_id)
    WHERE id = NEW.order_id;
END //
```
Trigger ini digunakan untuk otomatis akan update order totalnya ketika order item berubah

Beberapa peran trigger lainnya yang digunakan untuk sistem ini yaitu 
1. Trigger validasi ketersedian makanan sebelum dipesan
2. Trigger secara otomatis menghitung subtotal untuk item pesanan

### Transaction ###


### Stored Function ###


### Backup Otomatis ###
