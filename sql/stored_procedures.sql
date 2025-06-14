-- Stored Procedures for Orders
DELIMITER //

-- Get all orders with details
CREATE PROCEDURE get_all_orders()
BEGIN
    SELECT o.id, u.username, o.order_date, o.total_price, o.status, o.created_at,
           GROUP_CONCAT(CONCAT(m.name, ' (', oi.quantity, ')') SEPARATOR ', ') as meals
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    JOIN order_items oi ON o.id = oi.order_id
    JOIN meals m ON oi.meal_id = m.id
    GROUP BY o.id
    ORDER BY o.created_at DESC;
END //

-- Get recent orders
CREATE PROCEDURE get_recent_orders(IN p_limit INT)
BEGIN
    SELECT o.*, u.username 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT p_limit;
END //

-- Update order status
CREATE PROCEDURE update_order_status(IN p_order_id INT, IN p_status VARCHAR(20))
BEGIN
    UPDATE orders SET status = p_status WHERE id = p_order_id;
END //

-- Stored Procedures for Meals
-- Get all meals
CREATE PROCEDURE get_all_meals()
BEGIN
    SELECT * FROM meals ORDER BY name;
END //

-- Get available meals
CREATE PROCEDURE get_available_meals()
BEGIN
    SELECT * FROM meals WHERE available = 1 ORDER BY name;
END //

-- Get meal by ID
CREATE PROCEDURE get_meal_by_id(IN p_id INT)
BEGIN
    SELECT * FROM meals WHERE id = p_id;
END //

-- Add meal
CREATE PROCEDURE add_meal(IN p_name VARCHAR(100), IN p_description TEXT, IN p_price DECIMAL(10,2))
BEGIN
    INSERT INTO meals (name, description, price) VALUES (p_name, p_description, p_price);
END //

-- Update meal
CREATE PROCEDURE update_meal(IN p_id INT, IN p_name VARCHAR(100), IN p_description TEXT, IN p_price DECIMAL(10,2), IN p_available TINYINT)
BEGIN
    UPDATE meals 
    SET name = p_name, description = p_description, price = p_price, available = p_available 
    WHERE id = p_id;
END //

-- Delete meal
CREATE PROCEDURE delete_meal(IN p_id INT)
BEGIN
    DELETE FROM meals WHERE id = p_id;
END //

-- Stored Procedures for Users
-- Get user by ID
CREATE PROCEDURE get_user_by_id(IN p_id INT)
BEGIN
    SELECT id, username, email, role, created_at FROM users WHERE id = p_id;
END //

-- Get user by username or email
CREATE PROCEDURE get_user_by_username_or_email(IN p_username VARCHAR(100))
BEGIN
    SELECT id, username, email, password, role FROM users 
    WHERE username = p_username OR email = p_username;
END //

-- Register user
CREATE PROCEDURE register_user(IN p_username VARCHAR(50), IN p_email VARCHAR(100), IN p_password VARCHAR(255), IN p_role VARCHAR(10))
BEGIN
    INSERT INTO users (username, email, password, role) VALUES (p_username, p_email, p_password, p_role);
END //

DELIMITER ;
