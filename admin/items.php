<?php
require_once '../includes/admin_auth.php';

$success = '';
$error = '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. เพิ่มหมวดหมู่ใหม่
        if (isset($_POST['add_category']) && !empty(trim($_POST['new_category']))) {
            $new_category = trim($_POST['new_category']);
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$new_category]);
            $success = 'เพิ่มหมวดหมู่ใหม่เรียบร้อยแล้ว';
        }

        // 2. ลบหมวดหมู่
        if (isset($_POST['delete_category_id'])) {
            $delete_category_id = $_POST['delete_category_id'];

            // ตรวจสอบก่อนว่าหมวดหมู่นี้มีอุปกรณ์อยู่หรือไม่
            $stmt = $conn->prepare("SELECT COUNT(*) FROM items WHERE category_id = ?");
            $stmt->execute([$delete_category_id]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $error = 'ไม่สามารถลบหมวดหมู่นี้ได้ เนื่องจากยังมีอุปกรณ์อยู่ในหมวดหมู่';
            } else {
                $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$delete_category_id]);
                $success = 'ลบหมวดหมู่เรียบร้อยแล้ว';
            }
        }

        // 3. เพิ่มหรือแก้ไขอุปกรณ์
        if (isset($_POST['item_id']) || isset($_POST['name'])) {
            $item_id = $_POST['item_id'] ?? null;
            $name = $_POST['name'] ?? '';
            $category_id = $_POST['category_id'] ?? '';
            $stock_qty = $_POST['stock_qty'] ?? 0;
            $min_qty = $_POST['min_qty'] ?? 0;
            $description = $_POST['description'] ?? '';

            if ($item_id) {
                // Update existing item
                $stmt = $conn->prepare("
                    UPDATE items 
                    SET name = ?, category_id = ?, stock_qty = ?, min_qty = ?, description = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $category_id, $stock_qty, $min_qty, $description, $item_id]);
            } else {
                // Create new item
                $stmt = $conn->prepare("
                    INSERT INTO items (name, category_id, stock_qty, min_qty, description)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $category_id, $stock_qty, $min_qty, $description]);
            }
            $success = 'บันทึกข้อมูลเรียบร้อยแล้ว';
        }
    } catch (PDOException $e) {
        $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
        error_log($e->getMessage());
    }
}

// ดึงข้อมูลหมวดหมู่
try {
    $stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'เกิดข้อผิดพลาดในการโหลดข้อมูลหมวดหมู่';
    error_log($e->getMessage());
}

// ดึงข้อมูลอุปกรณ์พร้อมชื่อหมวดหมู่
try {
    $stmt = $conn->prepare("
        SELECT i.*, c.name as category_name
        FROM items i
        JOIN categories c ON i.category_id = c.id
        ORDER BY c.name, i.name
    ");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'เกิดข้อผิดพลาดในการโหลดข้อมูลอุปกรณ์';
    error_log($e->getMessage());
}

$pageTitle = 'จัดการอุปกรณ์';
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

<!-- ฟอร์มจัดการหมวดหมู่ -->
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <h3 class="text-lg font-semibold mb-4">จัดการหมวดหมู่</h3>

    <!-- ฟอร์มเพิ่มหมวดหมู่ -->
    <form method="POST" class="flex space-x-2 mb-4">
        <input type="text" name="new_category" placeholder="ชื่อหมวดหมู่ใหม่" required
            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        <button type="submit" name="add_category" value="1"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">เพิ่ม</button>
    </form>

    <!-- รายการหมวดหมู่พร้อมปุ่มลบ -->
    <ul class="divide-y divide-gray-200 max-h-48 overflow-auto">
        <?php foreach ($categories as $category): ?>
            <li class="flex justify-between items-center py-2">
                <span><?php echo htmlspecialchars($category['name']); ?></span>
                <form method="POST" onsubmit="return confirm('ต้องการลบหมวดหมู่นี้จริงหรือไม่?');">
                    <input type="hidden" name="delete_category_id" value="<?php echo $category['id']; ?>">
                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">ลบ</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- ฟอร์มเพิ่ม/แก้ไขอุปกรณ์ -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">เพิ่ม/แก้ไขอุปกรณ์</h3>
            <form method="POST" class="space-y-4" id="itemForm">
                <input type="hidden" name="item_id" id="item_id">

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">ชื่ออุปกรณ์</label>
                    <input type="text" id="name" name="name" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">หมวดหมู่</label>
                    <select id="category_id" name="category_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">เลือกหมวดหมู่</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="stock_qty" class="block text-sm font-medium text-gray-700">จำนวนคงเหลือ</label>
                    <input type="number" id="stock_qty" name="stock_qty" required min="0"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="min_qty" class="block text-sm font-medium text-gray-700">จำนวนขั้นต่ำ</label>
                    <input type="number" id="min_qty" name="min_qty" required min="0"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">รายละเอียด</label>
                    <textarea id="description" name="description" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="resetForm()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        ยกเลิก
                    </button>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- รายการอุปกรณ์ทั้งหมด -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">รายการอุปกรณ์ทั้งหมด</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ชื่อ</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    หมวดหมู่</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    คงเหลือ</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ขั้นต่ำ</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                        <?php if ($item['description']): ?>
                                            <div class="text-xs text-gray-500">
                                                <?php echo htmlspecialchars($item['description']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($item['category_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <span
                                            class="<?php echo $item['stock_qty'] <= $item['min_qty'] ? 'text-red-600 font-semibold' : ''; ?>">
                                            <?php echo $item['stock_qty']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo $item['min_qty']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <button onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)"
                                            class="text-blue-600 hover:text-blue-900">แก้ไข</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editItem(item) {
    document.getElementById('item_id').value = item.id;
    document.getElementById('name').value = item.name;
    document.getElementById('category_id').value = item.category_id;
    document.getElementById('stock_qty').value = item.stock_qty;
    document.getElementById('min_qty').value = item.min_qty;
    document.getElementById('description').value = item.description || '';

    // Scroll to form
    document.getElementById('itemForm').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('itemForm').reset();
    document.getElementById('item_id').value = '';
}
</script>

<?php
$content = ob_get_clean();
require_once '../includes/layout.php';
?>
