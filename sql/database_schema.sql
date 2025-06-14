-- mealprep Database Schema

-- FUNCTION: calculate_order_total
-- Menghitung total harga berdasarkan order_id
DELIMITER $$
CREATE FUNCTION calculate_order_total(p_order_id INT)
RETURNS DECIMAL(10,2)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE total DECIMAL(10,2);
    SELECT SUM(subtotal) INTO total
    FROM order_items
    WHERE order_id = p_order_id;
    RETURN IFNULL(total, 0);
END $$
DELIMITER ;

-- TRIGGER: update_order_total
-- Memperbarui total harga saat item ditambahkan
DELIMITER $$
CREATE TRIGGER trg_update_order_total
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders
    SET total_price = calculate_order_total(NEW.order_id)
    WHERE id = NEW.order_id;
END $$
DELIMITER ;

-- PROCEDURE: place_order
-- Prosedur pemesanan makanan oleh user
DELIMITER $$
CREATE PROCEDURE place_order(
    IN p_user_id INT,
    IN p_order_date DATE,
    IN p_meal_id INT,
    IN p_quantity INT
)
BEGIN
    DECLARE v_order_id INT;
    DECLARE v_price DECIMAL(10,2);
    DECLARE v_subtotal DECIMAL(10,2);

    START TRANSACTION;

    -- Cek apakah user sudah punya order hari ini
    SELECT id INTO v_order_id
    FROM orders
    WHERE user_id = p_user_id AND order_date = p_order_date
    LIMIT 1;

    -- Jika belum ada, buat order baru
    IF v_order_id IS NULL THEN
        INSERT INTO orders (user_id, order_date)
        VALUES (p_user_id, p_order_date);
        SET v_order_id = LAST_INSERT_ID();
    END IF;

    -- Ambil harga makanan
    SELECT price INTO v_price
    FROM meals
    WHERE id = p_meal_id AND available = TRUE;

    SET v_subtotal = v_price * p_quantity;

    -- Tambahkan item ke order_items
    INSERT INTO order_items (order_id, meal_id, quantity, subtotal)
    VALUES (v_order_id, p_meal_id, p_quantity, v_subtotal);

    COMMIT;
END $$
DELIMITER ;

-- PROCEDURE: cancel_order
-- Membatalkan pesanan dan menghapus itemnya
DELIMITER $$
CREATE PROCEDURE cancel_order(
    IN p_order_id INT
)
BEGIN
    DELETE FROM order_items WHERE order_id = p_order_id;
    DELETE FROM orders WHERE id = p_order_id;
END $$
DELIMITER ;

-- FUNCTION: get_user_orders
-- Mengambil riwayat pesanan user
DELIMITER $$
CREATE PROCEDURE get_user_orders(
    IN p_user_id INT
)
BEGIN
    SELECT o.id AS order_id, o.order_date, o.total_price, o.status,
           m.name AS meal_name, oi.quantity, oi.subtotal
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN meals m ON oi.meal_id = m.id
    WHERE o.user_id = p_user_id
    ORDER BY o.order_date DESC;
END $$
DELIMITER ;
