-- สร้างฐานข้อมูล
CREATE DATABASE IF NOT EXISTS food_recipe_db;
USE food_recipe_db;
-- ตาราง Food
CREATE TABLE foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nameTh VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- ตาราง Recipe (มีความสัมพันธ์ 1..* กับ Food)
CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    food_id INT NOT NULL,
    recipeName VARCHAR(255) NOT NULL,
    quantity FLOAT NOT NULL,
    unitName VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE
);
-- ข้อมูลตัวอย่าง
INSERT INTO foods (nameTh, category) VALUES 
('ผัดกะเพรา', 'อาหารไทย'),
('ต้มยำกุ้ง', 'อาหารไทย'),
('สปาเก็ตตี้', 'อาหารตะวันตก');
INSERT INTO recipes (food_id, recipeName, quantity, unitName) VALUES 
(1, 'หมูสับ', 200, 'กรัม'),
(1, 'ใบกะเพรา', 50, 'กรัม'),
(1, 'พริก', 10, 'เม็ด'),
(2, 'กุ้ง', 300, 'กรัม'),
(2, 'ข่า', 20, 'กรัม'),
(3, 'เส้นสปาเก็ตตี้', 250, 'กรัม');