<?php
session_start();
require_once '../database.php';
$isAdmin = $_SESSION['role'] === 'admin';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
        $category = $_POST['category'];
        $status = $_POST['status'];
        $department = $_SESSION['department'];

        // 1. Prepared Statement for INSERT
        $stmt = $conn->prepare('INSERT INTO resource (Resource_Name, Categories_ID, Status_ID, Department_ID) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('siii', $name, $category, $status, $department);

        if ($stmt->execute()) {
            echo 'Success';
            exit();
        } else {
            echo 'Error: ' . $stmt->error;
            exit();
        }
    }
}

// 2. Fetching the list (Static query, no user input, so $conn->query is safe)
$sql1 = "SELECT 
    r.Resource_ID, 
    r.Resource_Name, 
    r.Categories_ID, 
    r.Department_ID, 
    c.Category_Name, 
    s.Status_Name, 
    d.Department_Name 
FROM resource r 
LEFT JOIN categories c ON r.Categories_ID = c.Categories_ID 
LEFT JOIN equipment_status s ON r.Status_ID = s.Status_ID 
LEFT JOIN department d ON r.Department_ID = d.Department_ID
WHERE r.Department_ID = {$_SESSION['department']}
  AND r.is_deleted = 0; -- Only show active resources
";


$resources = $conn->query($sql1);

// resources.php

// 1. Get filters from the AJAX call
$searchTerm = $_GET['search'] ?? '';
$filterCategory = $_GET['filter_category'] ?? '';
$filterStatus = $_GET['filter_status'] ?? '';
$deptId = $_SESSION['department'];

$sql = "SELECT r.*, c.Category_Name, s.Status_Name 
        FROM resource r 
        LEFT JOIN categories c ON r.Categories_ID = c.Categories_ID 
        LEFT JOIN equipment_status s ON r.Status_ID = s.Status_ID 
        WHERE r.Department_ID = ? AND r.is_deleted = 0";

// Append filters dynamically
if (!empty($searchTerm)) $sql .= " AND r.Resource_Name LIKE ?";
if (!empty($filterCategory)) $sql .= " AND r.Categories_ID = ?";
if (!empty($filterStatus)) $sql .= " AND r.Status_ID = ?"; // New filter

$stmt = $conn->prepare($sql);

// Update Parameter Binding
$params = [$deptId];
$types = "i";

if (!empty($searchTerm)) {
    $params[] = "%$searchTerm%";
    $types .= "s";
}
if (!empty($filterCategory)) {
    $params[] = $filterCategory;
    $types .= "i";
}
if (!empty($filterStatus)) {
    $params[] = $filterStatus;
    $types .= "i";
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$resources = $stmt->get_result();


?>

<?php if ($isAdmin): ?>
    <!-- Floating Action Button -->
    <button id="toggle-add-form" class="fab-button">
        <span class="material-symbols-sharp">box_add</span>

    </button>
<?php endif; ?>
<div class="main-container">

</div>
<div class="resource-containers">

    <div class="table-controls">
        <form method="GET" action="" class="filter-form">
            <input type="text" name="search" placeholder="Search resources..."
                value="<?= htmlspecialchars($searchTerm) ?>">

            <select name="filter_category">
                <option value="">All Categories</option>
                <option value="1" <?= $filterCategory == '1' ? 'selected' : '' ?>>Laptop</option>
                <option value="2" <?= $filterCategory == '2' ? 'selected' : '' ?>>Projector</option>
                <option value="3" <?= $filterCategory == '3' ? 'selected' : '' ?>>Laboratory Equipment</option>
                <option value="4" <?= $filterCategory == '4' ? 'selected' : '' ?>>Smart TV</option>
                <option value="5" <?= $filterCategory == '5' ? 'selected' : '' ?>>Room</option>
            </select>

            <select name="filter_status">
                <option value="">All Statuses</option>
                <option value="1" <?= $filterStatus == '1' ? 'selected' : '' ?>>Available</option>
                <option value="2" <?= $filterStatus == '2' ? 'selected' : '' ?>>Reserved</option>
                <option value="3" <?= $filterStatus == '3' ? 'selected' : '' ?>>In Use</option>
                <option value="4" <?= $filterStatus == '4' ? 'selected' : '' ?>>Under Maintenance</option>
                <option value="5" <?= $filterStatus == '5' ? 'selected' : '' ?>>Returned</option>
            </select>
        </form>
    </div>
</div>


<div class="resource-containers">
    <div id="modal-overlay" class="modal-overlay">
        <div id="add-resource-section" class="container-1 modal-content">
            <div class="modal-header">
                <h1 class="title">Add Resource</h1>
                <span id="close-modal" class="material-icons close-btn">close</span>
            </div>

            <form id="resource-form" class="ajax-form" method="post" action="">
                <label>Name:</label>
                <input type="text" id="name" name="name" required>

                <label>Category:</label>
                <select id="category" name="category">
                    <option value="1">Laptop</option>
                    <option value="2">Projector</option>
                    <option value="3">Laboratory Equipment</option>
                    <option value="4">Smart TV</option>
                    <option value="5">Room</option>
                </select>

                <label>Status:</label>
                <select name="status" id="status">
                    <option value="1">Available</option>
                    <option value="2">Reserved</option>
                    <option value="3">In Use</option>
                    <option value="4">Under Maintenance</option>
                    <option value="5">Returned</option>
                </select>
                <br><br>
                <input type="submit" name="submit" value="Submit" style="width: 100%;">
            </form>
        </div>
    </div>

    <div class="dashboard-containers">
        <div class="resources" id="resources">
            <?php if ($resources && $resources->num_rows > 0): ?>
                <table class="resource-table">
                    <thead>
                        <tr>
                            <th>Resource ID</th>
                            <th>Resource Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th> <!-- New Header -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($resource = $resources->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($resource['Resource_ID']) ?></td>
                                <td><?= htmlspecialchars($resource['Resource_Name']) ?></td>
                                <td><?= htmlspecialchars($resource['Category_Name'] ?? 'No Category') ?></td>
                                <td><?php
                                    $status = strtolower($resource['Status_Name'] ?? 'no status');
                                    // Create a slug (e.g., "In Use" becomes "status-in-use")
                                    $statusClass = 'status-' . str_replace(' ', '-', $status);
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>-badge">
                                        <?= htmlspecialchars($resource['Status_Name'] ?? 'No Status') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-container">
                                        <?php if (strtolower($resource['Status_Name']) === 'available'): ?>
                                            <div class="user-controls">

                                                <!-- Reserve/Use Buttons -->
                                                <button type="button" class="reserve-use open-reserve-btn status-reserved"
                                                    data-id="<?= $resource['Resource_ID'] ?>"
                                                    data-resource-name="<?= htmlspecialchars($resource['Resource_Name']) ?>">
                                                    <span class="material-icons">schedule</span> Reserve
                                                </button>

                                                <button type="button" class="reserve-use open-use-btn status-available"
                                                    data-id="<?= $resource['Resource_ID'] ?>"
                                                    data-resource-name="<?= htmlspecialchars($resource['Resource_Name']) ?>">
                                                    <span class="material-icons">today</span> Use
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="status-badge status-unavailable-badge">
                                                <span class="material-icons" style="font-size: 14px; vertical-align: middle;">error_outline</span> Unavailable
                                            </span>
                                        <?php endif; ?>

                                        <!-- Admin Actions (Grouped inside the same container for alignment) -->
                                        <?php if (strtolower($_SESSION['role']) === 'admin'): ?>
                                            <div class="admin-controls">
                                                <button type="button" class="reserve-use open-update-btn"
                                                    data-id="<?= $resource['Resource_ID'] ?>"
                                                    data-name="<?= htmlspecialchars($resource['Resource_Name']) ?>"
                                                    data-cat="<?= $resource['Categories_ID'] ?>"
                                                    data-status="<?= $resource['Status_ID'] ?>"> <!-- Pass Status ID here -->
                                                    <span class="material-icons">edit</span> Edit
                                                </button>

                                                <form class="ajax-form delete-form" method="POST" action="views/delete_resource.php"
                                                    onsubmit="return confirm('Delete this resource permanently?');">
                                                    <input type="hidden" name="resource_id" value="<?= $resource['Resource_ID'] ?>">
                                                    <button type="submit" class="reserve-use" id="delete-btn">
                                                        <span class="material-icons">delete</span>
                                                    </button>
                                                </form>

                                            </div>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            <?php else: ?>
                <p>No resources found</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Match ID to your JS logic and add ajax-form class -->
    <div id="reserve-modal-overlay" class="modal-overlay">
        <div class="modal-content container-1">
            <div class="modal-header">
                <h1 class="title">Reserve <span id="res-name-display"></span></h1>
                <!-- Match ID to your JS logic -->
                <span id="close-reserve-modal" class="material-icons close-btn">close</span>
            </div>

            <!-- Added ajax-form class so your JS captures the submit -->
            <form id="reservation-form" class="ajax-form" action="views/update_status.php" method="POST">
                <!-- Match ID to your JS logic (reserve-resource-id) -->
                <input type="hidden" name="resource_id" id="reserve-resource-id">

                <label>Start Date & Time:</label>
                <input type="datetime-local" name="start_datetime" required>

                <label>End Date & Time:</label>
                <input type="datetime-local" name="end_datetime" required>

                <br><br>
                <input type="submit" value="Confirm Reservation" style="width: 100%;">
            </form>
        </div>
    </div>
    <div id="use-modal-overlay" class="modal-overlay">
        <div class="modal-content container-1">
            <div class="modal-header">
                <h1 class="title">Use <span id="use-name-display"></span></h1>
                <span id="close-use-modal" class="material-icons close-btn">close</span>
            </div>

            <form id="use-form" class="ajax-form" action="views/update_status.php" method="POST">
                <input type="hidden" name="resource_id" id="use-resource-id">
                <!-- Hidden status 3 for "In Use" -->
                <input type="hidden" name="status" value="3">

                <label>Start Date & Time (Current):</label>
                <input type="datetime-local" name="start_datetime" id="use-start-time" readonly>

                <label>Expected End Date & Time:</label>
                <input type="datetime-local" name="end_datetime" required>

                <br><br>
                <input type="submit" value="Start Using" style="width: 100%;">
            </form>
        </div>
    </div>
    <div id="update-modal-overlay" class="modal-overlay">
        <div class="modal-content container-1">
            <div class="modal-header">
                <h1 class="title">Edit Resource: <span id="edit-name-display"></span></h1>
                <span id="close-update-modal" class="material-icons close-btn">close</span>
            </div>

            <form id="update-form" class="ajax-form" action="views/update_resource.php" method="POST">
                <!-- Hidden ID to tell the database which record to change -->
                <input type="hidden" name="resource_id" id="update-resource-id">

                <label>Resource Name:</label>
                <input type="text" name="resource_name" id="update-resource-name" required>

                <label>Category:</label>
                <select name="categories_id" id="update-category" required>
                    <option value="">Select Category</option>
                    <?php
                    // Fetch categories from DB to populate the dropdown
                    $catResult = $conn->query("SELECT Categories_ID, Category_Name FROM categories");
                    while ($cat = $catResult->fetch_assoc()): ?>
                        <option value="<?= $cat['Categories_ID'] ?>"><?= htmlspecialchars($cat['Category_Name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <label>Status:</label>
                <select name="status_id" id="update-status" required>
                    <option value="">Select Status</option>
                    <?php
                    // Fetch available statuses from equipment_status table
                    $statusResult = $conn->query("SELECT Status_ID, Status_Name FROM equipment_status");
                    while ($status = $statusResult->fetch_assoc()): ?>
                        <option value="<?= $status['Status_ID'] ?>">
                            <?= htmlspecialchars($status['Status_Name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>


                <br><br>
                <input type="submit" value="Update Resource" style="width: 100%; cursor: pointer;">
            </form>
        </div>
    </div>



</div>