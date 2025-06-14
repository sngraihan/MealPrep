# 🍽️ MealPrep - Sistem Pemesanan Makanan Harian

Proyek ini merupakan sistem pemesanan makanan harian yang dibangun menggunakan PHP Native dan MySQL. Tujuannya adalah untuk mengelola pemesanan makanan secara efisien dan konsisten, dengan memanfaatkan stored procedure, trigger, transaction, dan stored function. Sistem ini juga dilengkapi mekanisme backup otomatis untuk menjaga keamanan data jika terjadi hal yang tidak diinginkan.

![Home](screenshots/home.png)

## 📌 Detail Konsep

### ⚠️ Disclaimer

Peran **stored procedure**, **trigger**, **transaction**, dan **stored function** dalam proyek ini dirancang khusus untuk kebutuhan sistem **MealPrep**. Penerapannya bisa berbeda pada sistem lain, tergantung arsitektur dan kebutuhan masing-masing sistem.

### 🧠 Stored Procedure 
Stored procedure bertindak seperti SOP internal yang menetapkan alur eksekusi berbagai operasi penting di sistem pemesanan makanan. Procedure ini disimpan langsung di lapisan database, sehingga dapat menjamin konsistensi, efisiensi, dan keamanan eksekusi, terutama dalam sistem multi-user.

![Procedure](screenshots/procedure.png)

Beberapa procedure penting yang digunakan:

#### `models/order_model.php`
* `place_order(p_user_id, p_order_date, p_meal_id, p_quantity)`: Mengelola pemesanan makanan dengan membuat order baru atau menambahkan item ke order yang sudah ada.
  ```php
  // Call the place_order stored procedure
  $stmt = $this->conn->prepare("CALL place_order(?, ?, ?, ?)");
  $result = $stmt->execute([$user_id, $order_date, $meal_id, $quantity]);
  ```

- `get_user_orders(p_user_id)`: Mengambil riwayat pesanan pengguna dengan detail makanan.

```php
// Call the get_user_orders stored procedure
$stmt = $this->conn->prepare("CALL get_user_orders(?)");
$stmt->execute([$user_id]);
```

- `cancel_order(p_order_id)`: Membatalkan pesanan dan menghapus semua item terkait.

```php
// Call the cancel_order stored procedure
$stmt = $this->conn->prepare("CALL cancel_order(?)");
return $stmt->execute([$order_id]);
```

#### `models/meal_model.php`

- `get_available_meals()`: Mengambil daftar makanan yang tersedia untuk dipesan.
- `add_meal(p_name, p_description, p_price)`: Menambahkan menu makanan baru.
- `update_meal(p_id, p_name, p_description, p_price, p_available)`: Memperbarui informasi makanan.
- `delete_meal(p_id)`: Menghapus atau menonaktifkan makanan (tergantung apakah sudah pernah dipesan).

Dengan menyimpan proses-proses ini di sisi database, sistem menjaga integritas data di level paling dasar, terlepas dari cara aplikasi mengaksesnya.

### 🚨 Trigger

Trigger `trg_update_order_total` berfungsi sebagai sistem otomatis yang menghitung ulang total harga pesanan setiap kali ada item baru yang ditambahkan. Trigger ini memastikan konsistensi data tanpa perlu intervensi manual dari aplikasi.

Trigger yang digunakan:

- `trg_update_order_total`: Otomatis menghitung total harga pesanan saat item ditambahkan.

```sql
CREATE TRIGGER trg_update_order_total
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders
    SET total_price = calculate_order_total(NEW.order_id)
    WHERE id = NEW.order_id;
END
```

- `trg_validate_meal_availability`: Memvalidasi ketersediaan makanan sebelum pesanan dibuat.
- `trg_calculate_subtotal`: Otomatis menghitung subtotal berdasarkan harga dan kuantitas.

### 🔄 Transaction (Transaksi)

Dalam sistem pemesanan makanan, sebuah transaksi seperti pembuatan pesanan tidak dianggap berhasil jika hanya sebagian prosesnya yang selesai. Semua langkah harus dijalankan hingga tuntas — jika salah satu gagal, seluruh proses dibatalkan.

#### `models/order_model.php`

- Implementasi transaction untuk `place_order`

```php
try {
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
```

#### `models/user_model.php`

- Implementasi transaction untuk registrasi pengguna

```php
try {
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
```

### 📺 Stored Function

Stored function digunakan untuk mengambil informasi atau melakukan kalkulasi tanpa mengubah data. Seperti layar monitor: hanya menampilkan atau menghitung data, tidak mengubah apapun.

Function yang digunakan:

- `calculate_order_total(p_order_id)`: Menghitung total harga berdasarkan semua item dalam pesanan.

```sql
CREATE FUNCTION calculate_order_total(p_order_id INT)
RETURNS DECIMAL(10,2)
DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE total DECIMAL(10,2);
    SELECT SUM(subtotal) INTO total
    FROM order_items
    WHERE order_id = p_order_id;
    RETURN IFNULL(total, 0);
END
```

- `get_available_meals_count()`: Menghitung jumlah makanan yang tersedia.
- `get_today_orders_count()`: Menghitung jumlah pesanan hari ini.
- `get_total_users()`: Menghitung total pengguna sistem.
- `check_email_exists(p_email)`: Memeriksa apakah email sudah terdaftar.
- `check_username_exists(p_username)`: Memeriksa apakah username sudah digunakan.

### 🔄 Backup Otomatis

Untuk menjaga ketersediaan dan keamanan data, sistem dilengkapi fitur backup otomatis menggunakan `mysqldump`. Backup dapat dilakukan manual melalui admin panel atau otomatis menggunakan task scheduler. Semua file disimpan di direktori `storage/backups`.

#### `controllers/backup.php`

```php
// Handle backup creation
if ($_POST && isset($_POST['create_backup'])) {
    $backup_dir = __DIR__ . '/../storage/backups/';
    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0777, true);
    }
    
    $filename = 'mealprep_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $filepath = $backup_dir . $filename;
    
    // Database credentials
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'mealprep';
    
    // Create backup using mysqldump
    $command = "mysqldump --host=$host --user=$username --password=$password $database > \"$filepath\"";
    $result = shell_exec($command);
}
```

#### `storage/mysqlbackup.bat` (untuk Windows)

```bat
@echo off
setlocal enabledelayedexpansion

set "backupDir=C:\laragon\www\MealPrep\storage\backups"
set "mysqlDir=C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin"

set "timestamp=%date:~6,4%-%date:~3,2%-%date:~0,2%_%time:~0,2%-%time:~3,2%"

"%mysqlDir%\mysqldump" -u root -p mealprep > "%backupDir%\backup_%timestamp%.sql"
```

## 🏗️ Struktur Proyek

```plaintext
MealPrep/
├── assets/
│   ├── css/
│   │   └── style.css              # Styling utama aplikasi
│   └── js/
│       └── main.js                # JavaScript utilities
├── config/
│   └── database.php               # Konfigurasi koneksi database
├── controllers/
│   ├── backup.php                 # Controller untuk backup database
│   ├── meals.php                  # Controller untuk pemesanan makanan
│   └── orders.php                 # Controller untuk manajemen pesanan
├── models/
│   ├── meal_model.php             # Model untuk data makanan
│   ├── order_model.php            # Model untuk data pesanan
│   └── user_model.php             # Model untuk data pengguna
├── sql/
│   ├── mealprep.sql               # Database schema dan data
│   ├── stored_procedures.sql      # Definisi stored procedures
│   ├── stored_functions.sql       # Definisi stored functions
│   └── triggers.sql               # Definisi triggers
├── storage/
│   ├── backups/                   # Direktori backup database
│   └── mysqlbackup.bat           # Script backup otomatis
├── views/
│   ├── layout/
│   │   ├── header.php             # Template header
│   │   ├── navbar.php             # Template navigasi
│   │   └── footer.php             # Template footer
│   ├── admin_backup.php           # Halaman backup admin
│   ├── admin_menu.php             # Halaman kelola menu
│   ├── admin_orders.php           # Halaman kelola pesanan
│   ├── dashboard.php              # Halaman dashboard
│   ├── meals.php                  # Halaman pemesanan makanan
│   └── orders.php                 # Halaman riwayat pesanan
├── index.php                      # Halaman utama
├── login.php                      # Halaman login
├── register.php                   # Halaman registrasi
└── logout.php                     # Script logout
```

## ✨ Fitur Sistem

### 👤 Fitur Pengguna

- **Registrasi & Login**: Sistem autentikasi dengan password hashing
- **Dashboard**: Ringkasan statistik dan pesanan terbaru
- **Pemesanan Makanan**: Interface untuk memesan makanan dengan kalkulasi otomatis
- **Riwayat Pesanan**: Melihat dan membatalkan pesanan
- **Profil Pengguna**: Manajemen informasi akun

### 👨‍💼 Fitur Admin

- **Kelola Menu**: CRUD operasi untuk menu makanan
- **Kelola Pesanan**: Update status pesanan dan monitoring
- **Backup Database**: Backup manual dan otomatis
- **Dashboard Admin**: Statistik lengkap sistem
- **Manajemen Pengguna**: Monitoring aktivitas pengguna

### 🔧 Fitur Teknis

- **Stored Procedures**: Logika bisnis di level database
- **Triggers**: Validasi dan kalkulasi otomatis
- **Transactions**: Konsistensi data terjamin
- **Backup Otomatis**: Perlindungan data dengan mysqldump
- **Responsive Design**: Interface yang mobile-friendly

## 🗄️ Database Schema

### Tabel Utama

- **users**: Data pengguna dan admin
- **meals**: Menu makanan dan informasi harga
- **orders**: Header pesanan pengguna
- **order_items**: Detail item dalam setiap pesanan

### Stored Procedures

- `place_order()`: Proses pemesanan makanan
- `cancel_order()`: Pembatalan pesanan
- `get_user_orders()`: Riwayat pesanan pengguna
- `get_all_orders()`: Semua pesanan (admin)
- `add_meal()`, `update_meal()`, `delete_meal()`: Manajemen menu
- `register_user()`: Registrasi pengguna baru

### Stored Functions

- `calculate_order_total()`: Kalkulasi total pesanan
- `get_available_meals_count()`: Jumlah menu tersedia
- `get_today_orders_count()`: Pesanan hari ini
- `check_email_exists()`, `check_username_exists()`: Validasi duplikasi

### Triggers

- `trg_update_order_total`: Update total saat item ditambah
- `trg_validate_meal_availability`: Validasi ketersediaan menu
- `trg_calculate_subtotal`: Kalkulasi subtotal otomatis

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 8.1+ (Native)
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5.3
- **Icons**: Font Awesome 6.0
- **Alerts**: SweetAlert2
- **Server**: Apache/Nginx
- **Tools**: phpMyAdmin, Laragon/XAMPP

## 🚀 Cara Menjalankan Proyek

### Prasyarat

- PHP 8.1 atau lebih tinggi
- MySQL 8.0 atau lebih tinggi
- Apache/Nginx web server
- phpMyAdmin (opsional)

### Langkah Instalasi

1. **Clone Repository**

```bash
git clone https://github.com/username/mealprep.git
cd mealprep
```

2. **Setup Database**

```bash
# Buat database baru
mysql -u root -p -e "CREATE DATABASE mealprep;"

# Import schema dan data
mysql -u root -p mealprep < sql/mealprep.sql
mysql -u root -p mealprep < sql/stored_procedures.sql
mysql -u root -p mealprep < sql/stored_functions.sql
mysql -u root -p mealprep < sql/triggers.sql
```

3. **Konfigurasi Database**

```php
// config/database.php
private $host = 'localhost';
private $db_name = 'mealprep';
private $username = 'root';
private $password = 'your_password';
```

4. **Setup Permissions**

```bash
# Berikan permission untuk direktori backup
chmod 755 storage/backups/
```

5. **Akses Aplikasi**

   - Buka browser: `http://localhost/mealprep`
   - Login Admin: `admin` / `admin123`
   - Login User: `john_doe` / `password123`

### Setup Backup Otomatis (Windows)

1. **Edit Path MySQL**

```bat
# storage/mysqlbackup.bat
set "mysqlDir=C:\path\to\your\mysql\bin"
```

2. **Setup Task Scheduler**

   - Buka Task Scheduler
   - Create Basic Task
   - Set trigger (daily/weekly)
   - Action: Start Program
   - Program: `C:\path\to\mealprep\storage\mysqlbackup.bat`

## 📝 Catatan Developer

### Koneksi Database

- Menggunakan PDO dengan prepared statements untuk keamanan
- Error mode diset ke `PDO::ERRMODE_EXCEPTION`
- Charset UTF-8 untuk mendukung karakter Indonesia
- Connection pooling untuk efisiensi

### Transaction Management

```php
// Pattern yang digunakan di seluruh aplikasi
try {
    $this->conn->beginTransaction();
    
    // Database operations
    $stmt = $this->conn->prepare("CALL some_procedure(?, ?)");
    $stmt->execute([$param1, $param2]);
    
    $this->conn->commit();
    return true;
} catch(PDOException $e) {
    if ($this->conn->inTransaction()) {
        $this->conn->rollBack();
    }
    error_log("Error: " . $e->getMessage());
    return false;
}
```

### Pemanggilan Stored Procedure

```php
// Contoh pemanggilan dengan parameter
$stmt = $this->conn->prepare("CALL place_order(?, ?, ?, ?)");
$stmt->execute([$user_id, $order_date, $meal_id, $quantity]);

// Untuk procedure yang mengembalikan result set
$stmt = $this->conn->prepare("CALL get_user_orders(?)");
$stmt->execute([$user_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### Security Features

- Password hashing menggunakan `password_hash()`
- Input validation dan sanitization
- SQL injection prevention dengan prepared statements
- Session management untuk autentikasi
- Role-based access control (Admin/User)

### Error Handling

- Centralized error logging
- User-friendly error messages
- Database error masking untuk security
- Transaction rollback pada error

## 🧩 Relevansi Proyek dengan Pemrosesan Data Terdistribusi

Sistem ini dirancang dengan memperhatikan prinsip-prinsip dasar pemrosesan data terdistribusi:

- **Konsistensi**: Semua operasi dieksekusi dengan stored procedure dan validasi terpusat di database
- **Reliabilitas**: Trigger dan transaction memastikan sistem tetap aman meskipun ada error atau interupsi
- **Integritas**: Dengan logika disimpan di dalam database, sistem tetap valid walaupun dipanggil dari banyak sumber
- **Availability**: Sistem backup otomatis menjamin ketersediaan data
- **Scalability**: Arsitektur yang memungkinkan penambahan fitur dan pengguna

## 📸 Screenshots

### Halaman Utama
![Halaman Utama](screenshots/home.png)

### Dashboard Admin
![Dashboard Admin](screenshots/admin_dashboard.png)

### Pemesanan Makanan
![Pemesanan Makanan](screenshots/meals.png)

### Kelola Menu
![Kelola Menu](screenshots/admin_menu.png)

### Backup Database
![Backup Database](screenshots/backup.png)

### Riwayat Pesanan
![Riwayat Pesanan](screenshots/orders.png)

---



