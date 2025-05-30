<?php
require_once '../includes/auth_middleware.php';

$success = false;
$error = '';

// Get available items
try {
    $stmt = $conn->prepare("
        SELECT i.id, i.name, i.stock_qty, c.name as category_name
        FROM items i
        JOIN categories c ON i.category_id = c.id
        WHERE i.stock_qty > 0
        ORDER BY c.name, i.name
    ");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group items by category
    $categories = [];
    foreach ($items as $item) {
        $categories[$item['category_name']][] = $item;
    }
} catch (PDOException $e) {
    $error = 'เกิดข้อผิดพลาดในการโหลดข้อมูล';
    error_log($e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // Create requisition
        $stmt = $conn->prepare("
            INSERT INTO requisitions (user_id, status, notes)
            VALUES (?, 'pending', ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $_POST['notes'] ?? '']);
        $requisition_id = $conn->lastInsertId();

        // Add requisition items
        $stmt = $conn->prepare("
            INSERT INTO requisition_items (requisition_id, item_id, qty)
            VALUES (?, ?, ?)
        ");

        $items = $_POST['items'] ?? [];
        $quantities = $_POST['quantities'] ?? [];

        foreach ($items as $index => $item_id) {
            if (!empty($quantities[$index])) {
                $stmt->execute([$requisition_id, $item_id, $quantities[$index]]);
            }
        }

        $conn->commit();
        $success = true;
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
        error_log($e->getMessage());
    }
}

$pageTitle = 'เบิกอุปกรณ์';
ob_start();
?>

<?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        บันทึกรายการเบิกเรียบร้อยแล้ว กรุณารอการอนุมัติ
        <div class="mt-2">
            <a href="/app/requisitions/history.php" class="text-green-700 underline">ดูประวัติการเบิก</a>
        </div>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow p-6">
    <form method="POST" class="space-y-6" id="requisitionForm">
        <div id="itemsContainer" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">รายการอุปกรณ์</label>
                    <select name="items[]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">เลือกรายการ</option>
                        <?php foreach ($categories as $category => $items): ?>
                            <optgroup label="<?php echo htmlspecialchars($category); ?>">
                                <?php foreach ($items as $item): ?>
                                    <option value="<?php echo $item['id']; ?>" data-stock="<?php echo $item['stock_qty']; ?>">
                                        <?php echo htmlspecialchars($item['name']); ?> (คงเหลือ: <?php echo $item['stock_qty']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">จำนวน</label>
                    <input type="number" name="quantities[]" required min="1" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div>
            <button type="button" id="addItem" 
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                + เพิ่มรายการ
            </button>
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">หมายเหตุ</label>
            <textarea id="notes" name="notes" rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="/app/requisitions/new.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                ยกเลิก
            </a>
            <button type="submit"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                บันทึก
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('addItem').addEventListener('click', function() {
    const container = document.getElementById('itemsContainer');
    const template = container.children[0].cloneNode(true);
    
    // Clear values
    template.querySelectorAll('select, input').forEach(el => el.value = '');
    
    container.appendChild(template);
});

// Validate quantity against stock
document.getElementById('requisitionForm').addEventListener('submit', function(e) {
    const selects = this.querySelectorAll('select[name="items[]"]');
    const quantities = this.querySelectorAll('input[name="quantities[]"]');
    let isValid = true;

    selects.forEach((select, index) => {
        if (select.value) {
            const option = select.options[select.selectedIndex];
            const stock = parseInt(option.dataset.stock);
            const quantity = parseInt(quantities[index].value);

            if (quantity > stock) {
                alert(`จำนวนที่เบิกต้องไม่เกินจำนวนคงเหลือ (${stock})`);
                isValid = false;
            }
        }
    });

    if (!isValid) {
        e.preventDefault();
    }
});
</script>

<?php
$content = ob_get_clean();
require_once '../includes/layout.php';
?> 