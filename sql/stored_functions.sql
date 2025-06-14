-- Stored Functions
DELIMITER //

-- Get total orders by user
CREATE FUNCTION get_total_orders_by_user(p_user_id INT) RETURNS INT
DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM orders WHERE user_id = p_user_id;
    RETURN total;
END //

-- Get total spent by user
CREATE FUNCTION get_total_spent(p_user_id INT) RETURNS DECIMAL(10,2)
DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE total DECIMAL(10,2);
    SELECT SUM(total_price) INTO total FROM orders WHERE user_id = p_user_id;
    RETURN IFNULL(total, 0);
END //

-- Get available meals count
CREATE FUNCTION get_available_meals_count() RETURNS INT
DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM meals WHERE available = 1;
    RETURN total;
END //

-- Get today's orders count
CREATE FUNCTION get_today_orders_count() RETURNS INT
DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM orders WHERE DATE(created_at) = CURDATE();
    RETURN total;
END //

-- Get total users
CREATE FUNCTION get_total_users() RETURNS INT
DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM users WHERE role = 'user';
    RETURN total;
END //

-- Check if email exists
CREATE FUNCTION check_email_exists(p_email VARCHAR(100)) RETURNS BOOLEAN
DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE result BOOLEAN;
    SELECT EXISTS(SELECT 1 FROM users WHERE email = p_email) INTO result;
    RETURN result;
END //

-- Check if username exists
CREATE FUNCTION check_username_exists(p_username VARCHAR(50)) RETURNS BOOLEAN
DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE result BOOLEAN;
    SELECT EXISTS(SELECT 1 FROM users WHERE username = p_username) INTO result;
    RETURN result;
END //

-- Check if user is admin
CREATE FUNCTION is_admin(p_user_id INT) RETURNS BOOLEAN
DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE result BOOLEAN;
    SELECT role = 'admin' INTO result FROM users WHERE id = p_user_id;
    RETURN result;
END //

-- Count all orders (for debugging)
CREATE FUNCTION count_all_orders() RETURNS INT
DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM orders;
    RETURN total;
END //

DELIMITER ;
