<?php
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/../config/database.php';

$auth = new Auth($conn);
$currentUser = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบเบิกจ่ายอุปกรณ์สำนักงาน</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4">    
            <div class="flex justify-between items-center h-16">
                <a href="../index.php" class="text-xl font-bold">ระบบเบิกจ่ายอุปกรณ์</a>
                <div class="flex items-center space-x-4">
                    <?php if ($auth->isLoggedIn()): ?>
                        <?php if ($auth->isAdmin()): ?>
                            <a href="/app/admin/dashboard.php" class="hover:text-gray-200">แดชบอร์ด</a>
                        <?php endif; ?>
                        <a href="/app/requisitions/new.php" class="hover:text-gray-200">เบิกอุปกรณ์</a>
                        <a href="/app/requisitions/history.php" class="hover:text-gray-200">ประวัติการเบิก</a>

                        <!-- เมนูโปรไฟล์แบบคลิก -->
                        <div class="relative">
                            <button onclick="toggleDropdown()" class="flex items-center space-x-1 hover:text-gray-200 focus:outline-none">
                                <span><?php echo htmlspecialchars($currentUser['name']); ?></span>
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div id="dropdownMenu" class="absolute right-0 w-48 py-2 mt-2 bg-white text-gray-800 rounded-lg shadow-xl hidden z-50">
                                <a href="/app/auth/logout.php" class="block px-4 py-2 hover:bg-gray-100">ออกจากระบบ</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/app/auth/login.php" class="hover:text-gray-200">เข้าสู่ระบบ</a>
                        <a href="/app/auth/register.php" class="hover:text-gray-200">ลงทะเบียน</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8">
        <?php if (isset($pageTitle)): ?>
            <h1 class="text-2xl font-bold mb-6"><?php echo htmlspecialchars($pageTitle); ?></h1>
        <?php endif; ?>
        
        <!-- Content will be inserted here -->
        <?php if (isset($content)) echo $content; ?>
    </main>

    <footer class="bg-gray-800 text-white py-4 mt-8">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; <?php echo date('Y'); ?> ระบบเบิกจ่ายอุปกรณ์สำนักงาน</p>
        </div>
    </footer>

    <!-- Script สำหรับเปิด/ปิดเมนู dropdown -->
    <script>
        function toggleDropdown() {
            const menu = document.getElementById('dropdownMenu');
            menu.classList.toggle('hidden');
        }

        document.addEventListener('click', function (e) {
            const button = e.target.closest('button');
            const menu = document.getElementById('dropdownMenu');
            if (!button && !e.target.closest('#dropdownMenu')) {
                menu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>