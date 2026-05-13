<?php
session_start();
require_once '../database.php';

$isAdmin = ($_SESSION['role'] === 'admin');

// Capture Filters
$searchUser = $_GET['search_user'] ?? '';
$searchResource = $_GET['search_resource'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$statusFilter = $_GET['status_filter'] ?? '';
$deptId = $_SESSION['department'];

// Define base query 
$sql = "SELECT 
    ra.Reservation_ID, 
    CONCAT(u.First_Name, ' ', u.Last_Name) AS Full_Name,
    d.Department_Name, 
    rb.Resource_Name, 
    ra.Reservation_Date, 
    ra.Return_Date
FROM reservation ra
LEFT JOIN users u ON ra.Users_ID = u.Users_ID
LEFT JOIN department d ON ra.Department_ID = d.Department_ID
LEFT JOIN resource rb ON ra.Resource_ID = rb.Resource_ID
WHERE ra.Department_ID = ?";

// START OF CHANGE: Check if user is NOT an admin
if (!$isAdmin) {
    $sql .= " AND ra.Users_ID = ?";
    $params = [$deptId, $_SESSION['userid']]; // Add userID to params
    $types = "ii"; // Two integers
} else {
    $params = [$deptId];
    $types = "i";
}

// 1. Filter by Name
if (!empty($searchUser)) {
    $sql .= " AND CONCAT(u.First_Name, ' ', u.Last_Name) LIKE ?";
    $params[] = "%$searchUser%";
    $types .= "s";
}

// 2. Filter by Resource
if (!empty($searchResource)) {
    $sql .= " AND rb.Resource_Name LIKE ?";
    $params[] = "%$searchResource%";
    $types .= "s";
}

// 3. Filter by Start Date (Minimum)
if (!empty($startDate)) {
    $sql .= " AND ra.Reservation_Date >= ?";
    $params[] = $startDate . " 00:00:00"; // Ensure it starts at the beginning of the day
    $types .= "s";
}

// 4. Filter by End Date (Maximum)
if (!empty($endDate)) {
    $sql .= " AND ra.Return_Date <= ?";
    $params[] = $endDate . " 23:59:59"; // Ensure it includes the whole end day
    $types .= "s";
}


// 5. Filter by Status (Active/Expired)
if ($statusFilter === 'active') {
    $sql .= " AND ra.Return_Date >= NOW()";
} elseif ($statusFilter === 'expired') {
    $sql .= " AND ra.Return_Date < NOW()";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$reservations = $stmt->get_result();

?>

<div class="reservation-container" style="flex: 1; display: flex; flex-direction: column;">
    <?php if ($isAdmin): ?>
        <button id="toggle-add-form" class="fab-button">
            <span class="material-symbols-sharp">calendar_add_on</span>
        </button>
    <?php endif; ?>

    <!-- 2. The Popup Overlay -->
    <div id="modal-overlay" class="modal-overlay">
        <div id="add-reservation-section" class="container-1 modal-content">
            <div class="modal-header">
                <h1 class="title">Add Reservation</h1>
                <span id="close-modal" class="material-icons close-btn">close</span>
            </div>

            <form id="reservation-form" class="ajax-form" method="post" action="views/reservations.php">
                <label>TR Code (User):</label>
                <input type="text" name="tr_code" placeholder="e.g. TR123" required>

                <label>Resource Name:</label>
                <input type="text" name="resource_name" placeholder="e.g. Laptop 01" required>

                <label>Start Date & Time:</label>
                <input type="datetime-local" name="date_initial" required>

                <label>End Date & Time:</label>
                <input type="datetime-local" name="date_final" required>

                <br><br>
                <input type="submit" name="submit" value="Reserve Now" style="width: 100%;">
            </form>
        </div>
    </div>

    <div class="table-controls">
        <form class="filter-form" onsubmit="return false;">
            <input type="text" name="search_user" placeholder="Search User..." value="<?= htmlspecialchars($searchUser) ?>">
            <input type="text" name="search_resource" placeholder="Search Resource..." value="<?= htmlspecialchars($searchResource) ?>">

            <select name="status_filter">
                <option value="">All Statuses</option>
                <option value="active" <?= $statusFilter == 'active' ? 'selected' : '' ?>>Active</option>
                <option value="expired" <?= $statusFilter == 'expired' ? 'selected' : '' ?>>Expired</option>
            </select>

            <div class="date-group">
                <label>From:</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
            </div>

            <div class="date-group">
                <label>To:</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
            </div>

            <!-- Optional: Clear button to quickly reset dates -->
            <!-- Inside your filter-form -->
            <button type="button" class="reset-filters btn-reset">
                <span class="material-icons">restart_alt</span> Clear Filters
            </button>

        </form>
    </div>


    <div class="dashboard-containers">
        <div class="resources" id="reservations">
            <?php if ($reservations && $reservations->num_rows > 0): ?>
                <table class="resource-table">
                    <thead>
                        <tr>
                            <th>Reservation ID</th>
                            <th>Reserved by</th>
                            <th>Resource used</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Status</th> <!-- Added status header -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($reservation = $reservations->fetch_assoc()):

                            $start = new DateTime($reservation['Reservation_Date']);
                            $end = new DateTime($reservation['Return_Date']);
                            $now = new DateTime(); // Get current date/time

                            // Check if start/end are same day for clean formatting
                            $isSameDay = $start->format('Y-m-d') === $end->format('Y-m-d');

                            // Determine Status
                            if ($now > $end) {
                                $statusLabel = 'Expired';
                                $statusClass = 'status-under-maintenance-badge';
                            } else {
                                $statusLabel = 'Active';
                                $statusClass = 'status-available-badge';
                            }
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($reservation['Reservation_ID']) ?></td>
                                <td><?= htmlspecialchars($reservation['Full_Name']) ?></td>
                                <td><?= htmlspecialchars($reservation['Resource_Name']) ?></td>
                                <td><?= $start->format('M j, Y - g:i A') ?></td>
                                <td>
                                    <?php if ($isSameDay): ?>
                                        <?= $end->format('g:i A') ?>
                                    <?php else: ?>
                                        <?= $end->format('M j, Y - g:i A') ?>
                                    <?php endif; ?>
                                </td>
                                <!-- New Status Column -->
                                <td>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                            </tr>
                        <?php
                        endwhile; ?>
                    </tbody>


                </table>
            <?php else: ?>
                <p>No resources found</p>
            <?php endif; ?>
        </div>
    </div>
</div>