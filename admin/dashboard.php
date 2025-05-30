<?php
require_once '../includes/admin_auth.php';

// Get statistics
$stats = [
    'pending_count' => 0,
    'approved_count' => 0,
    'rejected_count' => 0,
    'low_stock_count' => 0
];

try {
    // Get requisition statistics
    $stmt = $conn->prepare("
        SELECT status, COUNT(*) as count 
        FROM requisitions 
        WHERE status IN ('pending', 'approved', 'rejected')
        GROUP BY status
    ");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stats[$row['status'] . '_count'] = $row['count'];
    }

    // Get low stock items count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM items 
        WHERE stock_qty <= min_qty
    ");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['low_stock_count'] = $row['count'];

    // Get recent requisitions
    $stmt = $conn->prepare("
        SELECT r.id, r.status, r.created_at, u.name as user_name,
               GROUP_CONCAT(CONCAT(i.name, ' (', ri.qty, ')') SEPARATOR ', ') as items
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        JOIN requisition_items ri ON r.id = ri.requisition_id
        JOIN items i ON ri.item_id = i.id
        GROUP BY r.id
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recent_requisitions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get low stock items
    $stmt = $conn->prepare("
        SELECT name, stock_qty, min_qty
        FROM items
        WHERE stock_qty <= min_qty
        ORDER BY stock_qty ASC
        LIMIT 5
    ");
    $stmt->execute();
    $low_stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Handle error
    error_log($e->getMessage());
}

$pageTitle = 'แดชบอร์ดผู้ดูแลระบบ';
ob_start();
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Statistics Cards -->
    <div class="bg-blue-100 p-6 rounded-lg shadow">
        <div class="text-blue-800 text-xl font-bold"><?php echo $stats['pending_count']; ?></div>
        <div class="text-blue-600">รายการรออนุมัติ</div>
    </div>
    
    <div class="bg-green-100 p-6 rounded-lg shadow">
        <div class="text-green-800 text-xl font-bold"><?php echo $stats['approved_count']; ?></div>
        <div class="text-green-600">รายการอนุมัติแล้ว</div>
    </div>
    
    <div class="bg-red-100 p-6 rounded-lg shadow">
        <div class="text-red-800 text-xl font-bold"><?php echo $stats['rejected_count']; ?></div>
        <div class="text-red-600">รายการที่ปฏิเสธ</div>
    </div>
    
    <div class="bg-yellow-100 p-6 rounded-lg shadow">
        <div class="text-yellow-800 text-xl font-bold"><?php echo $stats['low_stock_count']; ?></div>
        <div class="text-yellow-600">รายการสต๊อกต่ำ</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Requisitions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">รายการเบิกล่าสุด</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">ผู้เบิก</th>
                        <th class="text-left py-2">รายการ</th>
                        <th class="text-left py-2">สถานะ</th>
                        <th class="text-left py-2">วันที่</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_requisitions as $req): ?>
                        <tr class="border-b">
                            <td class="py-2"><?php echo htmlspecialchars($req['user_name']); ?></td>
                            <td class="py-2"><?php echo htmlspecialchars($req['items']); ?></td>
                            <td class="py-2">
                                <span class="px-2 py-1 rounded text-sm
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
                            <td class="py-2"><?php echo date('d/m/Y', strtotime($req['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <a href="../admin/requisitions.php" class="text-blue-600 hover:text-blue-800">ดูทั้งหมด →</a>
        </div>
    </div>

    <!-- Low Stock Items -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">รายการสต๊อกต่ำ</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">รายการ</th>
                        <th class="text-left py-2">คงเหลือ</th>
                        <th class="text-left py-2">ขั้นต่ำ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($low_stock_items as $item): ?>
                        <tr class="border-b">
                            <td class="py-2"><?php echo htmlspecialchars($item['name']); ?></td>
                            <td class="py-2"><?php echo $item['stock_qty']; ?></td>
                            <td class="py-2"><?php echo $item['min_qty']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <a href="../admin/items.php" class="text-blue-600 hover:text-blue-800">จัดการสต๊อก →</a>
        </div>
    </div>
</div>

<div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
    <a href="../admin/requisitions.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
        <h3 class="text-lg font-semibold mb-2">จัดการคำขอเบิก</h3>
        <p class="text-gray-600">อนุมัติหรือปฏิเสธคำขอเบิกอุปกรณ์</p>
    </a>
    
    <a href="../admin/items.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
        <h3 class="text-lg font-semibold mb-2">จัดการอุปกรณ์</h3>
        <p class="text-gray-600">เพิ่ม/แก้ไขรายการอุปกรณ์และสต๊อก</p>
    </a>
    
    <a href="../admin/users.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
        <h3 class="text-lg font-semibold mb-2">จัดการผู้ใช้</h3>
        <p class="text-gray-600">ดูและจัดการข้อมูลผู้ใช้งาน</p>
    </a>
</div>

<?php
$content = ob_get_clean();
require_once '../includes/layout.php';
?> 