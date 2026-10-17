# 🍽️ Food Recipe REST API (code_food)

ระบบ RESTful Web Service สำหรับจัดการข้อมูลเมนูอาหารและรายการสูตรวัตถุดิบ พัฒนาด้วยภาษา **PHP เชิงวัตถุ (OOP)** เชื่อมต่อฐานข้อมูล **MySQL ด้วย PDO** ตามข้อกำหนด Class Diagram มีการแบ่งสถาปัตยกรรมแบบแยกชั้น (Layered Architecture) และส่งข้อมูลตอบกลับในรูปแบบ JSON มาตรฐาน

---

## 1. ข้อมูลระบบและความต้องการ (System Requirements)

| รายการ | รายละเอียด |
| :--- | :--- |
| **ภาษาที่ใช้** | PHP 8.0+ (Strict Types, Typed Properties, `match` Expression) |
| **PHP Extensions** | `pdo`, `pdo_mysql`, `json` |
| **ฐานข้อมูล** | MySQL 5.7+ / MariaDB 10.4+ |
| **เว็บเซิร์ฟเวอร์** | Apache (XAMPP Environment) |
| **ตำแหน่งโฟลเดอร์** | `C:\xampp\htdocs\OOP\code_food` |
| **การเข้ารหัส** | UTF-8 (`utf8mb4`) |

---

## 2. โครงสร้างโฟลเดอร์ของโปรเจกต์ (Project Structure)

| ไดเรกทอรี / ไฟล์ | หน้าที่และความรับผิดชอบ |
| :--- | :--- |
| `database/schema.sql` | สคริปต์ SQL สร้างฐานข้อมูล ตาราง `foods`, `recipes` พร้อม Foreign Key CASCADE |
| `api/config/database.php` | คลาส `Database` เชื่อมต่อ MySQL ด้วย PDO พร้อมดักจับ Exception ส่ง HTTP 503 |
| `api/controllers/FoodController.php` | คลาสควบคุม Routing, Data Validation และการจัดการเมนูอาหาร |
| `api/controllers/RecipeController.php` | คลาสควบคุม Routing, Data Validation และการจัดการสูตรและวัตถุดิบ |
| `api/models/Food.php` | คลาส Model เมนูอาหาร (มีเมธอด `init`, `display`, `addRecipe`, `getAll` ฯลฯ) |
| `api/models/Recipe.php` | คลาส Model วัตถุดิบ (มีเมธอด `getAllByFoodId`, `getOne`, `create` ฯลฯ) |
| `api/helpers/Response.php` | Static Helper จัดรูปแบบ JSON Response และ HTTP Status Code |
| `api/endpoints/foods.php` | Entry Point และจัดการ CORS Headers สำหรับเมนูอาหาร |
| `api/endpoints/recipes.php` | Entry Point และจัดการ CORS Headers สำหรับวัตถุดิบ |
| `README.md` | เอกสารคู่มือและข้อกำหนดของระบบ |

---

## 3. เอกสารโครงสร้างคลาสเชิงวัตถุ (Class Specifications)

### 3.1 คลาส `Food` (`api/models/Food.php`)

| Visibility | สมาชิก / เมธอด | ชนิดข้อมูล / Return | คำอธิบายการทำงาน |
| :--- | :--- | :--- | :--- |
| `public` | `$id` | `int` | รหัสเฉพาะของเมนูอาหาร |
| `public` | `$nameTh` | `string` | ชื่อเมนูอาหารภาษาไทย |
| `public` | `$category` | `string` | หมวดหมู่อาหาร |
| `public` | `$recipeList` | `array` | รายการวัตถุดิบทั้งหมดที่อยู่ในอาหารจานนี้ |
| `public` | `init($name, $category)` | `void` | กำหนดค่าเริ่มต้นของชื่ออาหารและหมวดหมู่ |
| `public` | `display()` | `?array` | ดึงข้อมูลอาหาร 1 รายการพร้อมวัตถุดิบทั้งหมดด้วย SQL `LEFT JOIN` |
| `public` | `addRecipe($recipe)` | `int` | เพิ่มวัตถุดิบเข้าสู่อาหารเมนูนี้ และคืนค่า `lastInsertId` |
| `public` | `getAll()` | `array` | ดึงรายการอาหารทั้งหมดในระบบ |
| `public` | `create()` | `int` | บันทึกอาหารใหม่ลงฐานข้อมูล และคืนค่า `id` |
| `public` | `update()` | `bool` | แก้ไขข้อมูลอาหารตาม `$id` |
| `public` | `delete()` | `bool` | ลบอาหารตาม `$id` (Cascade ลบ Recipe อัตโนมัติ) |
| `public` | `exists()` | `bool` | ตรวจสอบว่ามี `$id` นี้อยู่ในระบบหรือไม่ |
| `private` | `validate()` | `void` | ตรวจสอบว่า `$nameTh` และ `$category` ไม่เป็นค่าว่าง |

### 3.2 คลาส `Recipe` (`api/models/Recipe.php`)

| Visibility | สมาชิก / เมธอด | ชนิดข้อมูล / Return | คำอธิบายการทำงาน |
| :--- | :--- | :--- | :--- |
| `public` | `$id` | `int` | รหัสเฉพาะของรายการวัตถุดิบ |
| `public` | `$food_id` | `int` | รหัสอาหารที่เชื่อมโยง (Foreign Key) |
| `public` | `$recipeName` | `string` | ชื่อวัตถุดิบ |
| `public` | `$quantity` | `float` | ปริมาณวัตถุดิบ (ต้องมากกว่า 0) |
| `public` | `$unitName` | `string` | หน่วยนับ (เช่น กรัม, ฟอง, ช้อนโต๊ะ) |
| `public` | `getAllByFoodId()` | `array` | ดึงวัตถุดิบทั้งหมดของอาหารตาม `$food_id` |
| `public` | `getOne()` | `?array` | ดึงข้อมูลวัตถุดิบเดี่ยวตาม `$id` |
| `public` | `create()` | `int` | บันทึกวัตถุดิบใหม่ลงฐานข้อมูล |
| `public` | `update()` | `bool` | แก้ไขชื่อ ปริมาณ และหน่วยนับตาม `$id` |
| `public` | `delete()` | `bool` | ลบวัตถุดิบตาม `$id` |
| `public` | `foodExists()` | `bool` | ตรวจสอบว่า `$food_id` มีอยู่จริงในตาราง `foods` |
| `private` | `validate()` | `void` | ตรวจสอบความถูกต้องของค่าตัวแปรทั้งหมด |

---

## 4. ตารางข้อกำหนด API (API Specification)

**Base URL:** `http://localhost/OOP/code_food/api/endpoints`  
**Request Header:** `Content-Type: application/json`

### 4.1 เมนูอาหาร (`foods.php`)

| Method | Endpoint | Query / Body Parameters | หน้าที่การทำงาน | Status Codes |
| :---: | :--- | :--- | :--- | :---: |
| `GET` | `/foods.php` | — | ดึงรายการอาหารทั้งหมด | `200 OK` |
| `GET` | `/foods.php?id={id}` | `?id=1` | ดึงข้อมูลอาหารพร้อมรายการวัตถุดิบทั้งหมด | `200 OK`, `404 Not Found` |
| `POST` | `/foods.php` | `{"nameTh": "ข้าวผัด", "category": "อาหารไทย"}` | สร้างเมนูอาหารใหม่ | `201 Created`, `422 Unprocessable` |
| `PUT` | `/foods.php` | `{"id": 1, "nameTh": "...", "category": "..."}` | แก้ไขข้อมูลเมนูอาหาร | `200 OK`, `404`, `422` |
| `DELETE` | `/foods.php?id={id}` | `?id=1` | ลบเมนูอาหาร (Cascade ลบวัตถุดิบด้วย) | `200 OK`, `404 Not Found` |

### 4.2 วัตถุดิบและสูตรอาหาร (`recipes.php`)

| Method | Endpoint | Query / Body Parameters | หน้าที่การทำงาน | Status Codes |
| :---: | :--- | :--- | :--- | :---: |
| `GET` | `/recipes.php?food_id={id}` | `?food_id=1` | ดึงวัตถุดิบทั้งหมดของอาหารเมนูนั้น | `200 OK` |
| `GET` | `/recipes.php?id={id}` | `?id=1` | ดึงข้อมูลวัตถุดิบรายรายการ | `200 OK`, `404 Not Found` |
| `POST` | `/recipes.php` | `{"food_id": 1, "recipeName": "...", "quantity": 100, "unitName": "..."}` | เพิ่มวัตถุดิบลงในเมนูอาหาร | `201 Created`, `404`, `422` |
| `PUT` | `/recipes.php` | `{"id": 1, "recipeName": "...", "quantity": 150, "unitName": "..."}` | แก้ไขข้อมูลวัตถุดิบ | `200 OK`, `404`, `422` |
| `DELETE` | `/recipes.php?id={id}` | `?id=1` | ลบวัตถุดิบตามรหัส | `200 OK`, `404 Not Found` |

---

## 5. ตารางสรุป HTTP Status Codes

| Status Code | ข้อความมาตรฐาน | ความหมายและการนำไปใช้ในระบบ |
| :---: | :--- | :--- |
| `200` | **OK** | ดึงข้อมูล, แก้ไขข้อมูล (PUT) หรือลบข้อมูล (DELETE) สำเร็จ |
| `201` | **Created** | สร้างเมนูอาหารหรือวัตถุดิบใหม่ (POST) สำเร็จ |
| `204` | **No Content** | การตอบกลับ CORS Preflight Request (`OPTIONS`) |
| `400` | **Bad Request** | ส่งคำขอผิดพลาดหรือไม่ถูกต้องตามรูปแบบ |
| `404` | **Not Found** | ค้นหาข้อมูลตาม ID ไม่พบในระบบ |
| `405` | **Method Not Allowed** | เรียกใช้ HTTP Method ที่ไม่รองรับ |
| `422` | **Unprocessable Entity** | ข้อมูลไม่ผ่านการตรวจสอบ (เช่น ส่งค่าว่าง หรือปริมาณ <= 0) |
| `500` | **Internal Server Error** | เกิดข้อผิดพลาดที่ไม่คาดคิดขึ้นบนเซิร์ฟเวอร์ |
| `503` | **Service Unavailable** | ไม่สามารถเชื่อมต่อฐานข้อมูล MySQL ได้ (ดักจับผ่าน try-catch) |

---

## 6. ขั้นตอนการติดตั้งและทดสอบระบบ (Setup & Testing)

1. **การจัดวางไฟล์:** วางโฟลเดอร์โปรเจกต์ไว้ที่:
   ```text
   C:\xampp\htdocs\OOP\code_food