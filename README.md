# Office Supplies Requisition System

ระบบ Web Application สำหรับเบิกพัสดุสำนักงาน (Office Supplies Requisition System) พัฒนาด้วย PHP 8, MySQL และ Tailwind CSS

## 🔧 เทคโนโลยีที่ใช้

- PHP 8.x
- MySQL (ดูโครงสร้างใน `database/schema.sql` หรือ `office_supplies.sql`)
- Tailwind CSS (ใช้ตกแต่ง UI)
- HTML / CSS / JavaScript (พื้นฐาน)

## 📁 โครงสร้างโฟลเดอร์

- `admin/` - หน้าสำหรับผู้ดูแล เช่น จัดการผู้ใช้ รายการพัสดุ ประวัติการเบิก
- `auth/` - ระบบล็อกอิน ล็อกเอาต์ และลงทะเบียน
- `config/` - การตั้งค่าการเชื่อมต่อฐานข้อมูล (`database.php`)
- `database/` - ไฟล์ SQL สำหรับสร้างฐานข้อมูล
- `includes/` - ไฟล์รวมฟังก์ชันช่วยเหลือ เช่น ตรวจสอบสิทธิ์ (`auth_middleware.php`)
- `requisitions/` - หน้าสำหรับผู้ใช้งานทั่วไปเพื่อเบิกพัสดุ หรือดูประวัติ

## ✅ คุณสมบัติหลัก

- 🔐 ระบบล็อกอิน / ลงทะเบียนผู้ใช้งาน
- 👥 แยกระดับสิทธิ์ผู้ใช้ (admin / user)
- 📦 ระบบเบิกพัสดุ
- 📜 ประวัติการเบิกย้อนหลัง
- ⚙️ ผู้ดูแลจัดการข้อมูลพัสดุ / ผู้ใช้

## ⚙️ การติดตั้ง

1. ติดตั้ง PHP 8.x และ MySQL บนเครื่องหรือใช้ XAMPP
2. Clone หรือดาวน์โหลดโปรเจกต์นี้
3. สร้างฐานข้อมูลใน MySQL และนำเข้าไฟล์ `office_supplies.sql` หรือ `database/schema.sql`
4. ตั้งค่าเชื่อมต่อฐานข้อมูลใน `config/database.php`
5. เปิดใช้งานผ่าน localhost เช่น:
