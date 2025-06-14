-- Essential Database Triggers for MealPrep System
DELIMITER //

-- 1. Trigger to automatically update order total when order items change
DROP TRIGGER IF EXISTS trg_update_order_total //
CREATE TRIGGER trg_update_order_total
    AFTER INSERT ON order_items
    FOR EACH ROW
BEGIN
    UPDATE orders 
    SET total_price = calculate_order_total(NEW.order_id)
    WHERE id = NEW.order_id;
END //

-- 2. Trigger to validate meal availability before ordering
DROP TRIGGER IF EXISTS trg_validate_meal_availability //
CREATE TRIGGER trg_validate_meal_availability
    BEFORE INSERT ON order_items
    FOR EACH ROW
BEGIN
    DECLARE meal_available TINYINT DEFAULT 0;
    
    SELECT available INTO meal_available 
    FROM meals 
    WHERE id = NEW.meal_id;
    
    IF meal_available = 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Cannot order unavailable meal';
    END IF;
END //

-- 3. Trigger to automatically calculate subtotal for order items
DROP TRIGGER IF EXISTS trg_calculate_subtotal //
CREATE TRIGGER trg_calculate_subtotal
    BEFORE INSERT ON order_items
    FOR EACH ROW
BEGIN
    DECLARE meal_price DECIMAL(10,2);
    
    SELECT price INTO meal_price 
    FROM meals 
    WHERE id = NEW.meal_id;
    
    SET NEW.subtotal = meal_price * NEW.quantity;
END //

DELIMITER ;
