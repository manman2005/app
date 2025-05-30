<?php
require_once 'config/database.php';
require_once 'includes/Auth.php';

$auth = new Auth($conn);
$currentUser = $auth->getCurrentUser();

$pageTitle = 'หน้าหลัก';
ob_start();
?>

<div class="max-w-7xl mx-auto">
    <?php if ($auth->isLoggedIn()): ?>
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4">ยินดีต้อนรับ, <?php echo htmlspecialchars($currentUser['name']); ?></h2>
            
            <!-- เมนูหลักแบบใหม่ -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- เบิกอุปกรณ์ -->
                <a href="requisitions/new.php" 
                class="block p-6 bg-blue-50 rounded-xl shadow-md hover:shadow-lg hover:ring-2 hover:ring-blue-300 transform hover:scale-105 transition duration-300">
                    <div class="flex items-center mb-2">
                        <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <!-- เปลี่ยนเป็นไอคอนลูกศรไปข้างหน้า (การดำเนินการ) -->
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H9" />
                        </svg>
                        <h3 class="text-lg font-semibold text-blue-700">เบิกอุปกรณ์</h3>
                    </div>
                    <p class="text-blue-600">ยื่นคำขอเบิกอุปกรณ์สำนักงาน</p>
                </a>

                <!-- ประวัติการเบิก -->
                <a href="requisitions/history.php" 
                class="block p-6 bg-green-50 rounded-xl shadow-md hover:shadow-lg hover:ring-2 hover:ring-green-300 transform hover:scale-105 transition duration-300">
                    <div class="flex items-center mb-2">
                        <svg class="w-6 h-6 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <!-- เปลี่ยนเป็นไอคอนนาฬิกา (ประวัติย้อนหลัง) -->
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-green-700">ประวัติการเบิก</h3>
                    </div>
                    <p class="text-green-600">ดูประวัติและสถานะการเบิก</p>
                </a>

                <!-- แดชบอร์ดผู้ดูแล -->
                <?php if ($auth->isAdmin()): ?>
                    <a href="admin/dashboard.php" 
                       class="block p-6 bg-purple-50 rounded-xl shadow-md hover:shadow-lg hover:ring-2 hover:ring-purple-300 transform hover:scale-105 transition duration-300">
                        <div class="flex items-center mb-2">
                            <svg class="w-6 h-6 text-purple-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17a4 4 0 100-8 4 4 0 000 8zm5-9h.01M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            <h3 class="text-lg font-semibold text-purple-700">แดชบอร์ดผู้ดูแล</h3>
                        </div>
                        <p class="text-purple-600">จัดการระบบและดูรายงาน</p>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php
        // คำสั่ง SQL แสดงเบิกล่าสุด  
        try {
            $stmt = $conn->prepare("
                SELECT r.id, r.status, r.created_at,
                       GROUP_CONCAT(CONCAT(i.name, ' (', ri.qty, ')') SEPARATOR ', ') as items
                FROM requisitions r
                JOIN requisition_items ri ON r.id = ri.requisition_id
                JOIN items i ON ri.item_id = i.id
                WHERE r.user_id = ?
                GROUP BY r.id
                ORDER BY r.created_at DESC
                LIMIT 5
            ");
            $stmt->execute([$currentUser['id']]);
            $recent_requisitions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($recent_requisitions)):
        ?>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-4">การเบิกล่าสุดของคุณ</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">วันที่</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">รายการ</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recent_requisitions as $req): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('d/m/Y H:i', strtotime($req['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($req['items']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full
                                            <?php echo match($req['status']) {
                                                'pending' => 'bg-blue-100 text-blue-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                default => ''
                                            }; ?>">
                                            <?php echo match($req['status']) {
                                                'pending' => 'รออนุมัติ',
                                                'approved' => 'อนุมัติแล้ว',
                                                'rejected' => 'ปฏิเสธ',
                                                default => $req['status']
                                            }; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <a href="requisitions/history.php" class="text-blue-600 hover:text-blue-800">ดูทั้งหมด →</a>
                </div>
            </div>
        <?php 
            endif;
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
        ?>

    <?php else: ?>
        <div class="text-center py-16">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">ระบบเบิกจ่ายอุปกรณ์สำนักงาน</h1>
            <p class="text-xl text-gray-600 mb-8">ระบบจัดการการเบิกจ่ายอุปกรณ์สำนักงานแบบออนไลน์</p>
            <div class="space-x-4">
                <a href="auth/login.php" 
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    เข้าสู่ระบบ
                </a>
                <a href="auth/register.php"
                    class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    ลงทะเบียน
                </a>
            </div>
        </div>

        <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">ง่ายต่อการใช้งาน</h3>
                <p class="text-gray-600">ระบบออกแบบมาให้ใช้งานง่าย สะดวก รวดเร็ว</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">ติดตามสถานะได้ทันที</h3>
                <p class="text-gray-600">ตรวจสอบสถานะการเบิกได้แบบเรียลไทม์</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">ระบบแจ้งเตือนอัตโนมัติ</h3>
                <p class="text-gray-600">แจ้งเตือนเมื่อสต๊อกต่ำและอัพเดทสถานะ</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once 'includes/layout.php';
?>
