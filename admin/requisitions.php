<?php
require_once '../includes/admin_auth.php';

$success = '';
$error = '';
$search = $_GET['search'] ?? '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        $requisition_id = $_POST['requisition_id'] ?? null;
        $action = $_POST['action'] ?? '';
        $notes = $_POST['notes'] ?? '';

        if ($requisition_id && ($action === 'approve' || $action === 'reject')) {
            // Update requisition status
            $stmt = $conn->prepare("UPDATE requisitions SET status = ?, notes = CONCAT(COALESCE(notes, ''), '\n', ?) WHERE id = ?");
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $stmt->execute([$status, $notes, $requisition_id]);

            // If approved, update stock quantities
            if ($action === 'approve') {
                $stmt = $conn->prepare("SELECT item_id, qty FROM requisition_items WHERE requisition_id = ?");
                $stmt->execute([$requisition_id]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $update_stmt = $conn->prepare("UPDATE items SET stock_qty = stock_qty - ? WHERE id = ?");
                foreach ($items as $item) {
                    $update_stmt->execute([$item['qty'], $item['item_id']]);
                }
            }

            $conn->commit();
            $success = 'อัพเดทสถานะเรียบร้อยแล้ว';
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = 'เกิดข้อผิดพลาดในการอัพเดทข้อมูล';
        error_log($e->getMessage());
    }
}

// Get requisitions with optional search
try {
    $query = "
        SELECT r.id, r.status, r.created_at, r.notes,
               u.name as user_name,
               GROUP_CONCAT(CONCAT(i.name, ' (', ri.qty, ')') SEPARATOR ', ') as items
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        JOIN requisition_items ri ON r.id = ri.requisition_id
        JOIN items i ON ri.item_id = i.id
    ";
    $params = [];
    if ($search) {
        $query .= "WHERE u.name LIKE ? OR i.name LIKE ? ";
        $params = ["%$search%", "%$search%"];
    }
    $query .= "GROUP BY r.id ORDER BY 
        CASE r.status WHEN 'pending' THEN 1 WHEN 'approved' THEN 2 WHEN 'rejected' THEN 3 END,
        r.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $requisitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'เกิดข้อผิดพลาดในการโหลดข้อมูล';
    error_log($e->getMessage());
}

$pageTitle = 'จัดการคำขอเบิก';
ob_start();
?>

<?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <form method="GET" class="mb-4 flex items-center space-x-2">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="ค้นหาชื่อผู้เบิกหรือรายการ"
                   class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">
            <button type="submit"
                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded text-white bg-blue-600 hover:bg-blue-700">
                🔍 ค้นหา
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้เบิก</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">รายการ</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">หมายเหตุ</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การดำเนินการ</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($requisitions as $req): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo date('d/m/Y H:i', strtotime($req['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($req['user_name']); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <?php echo htmlspecialchars($req['items']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
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
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <?php echo nl2br(htmlspecialchars($req['notes'] ?? '')); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php if ($req['status'] === 'pending'): ?>
                                <form method="POST" class="flex space-x-2">
                                    <input type="hidden" name="requisition_id" value="<?php echo $req['id']; ?>">
                                    <input type="text" name="notes" placeholder="หมายเหตุ" class="flex-1 min-w-0 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    <button type="submit" name="action" value="approve" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700">อนุมัติ</button>
                                    <button type="submit" name="action" value="reject" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700">ปฏิเสธ</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once '../includes/layout.php';
?>
