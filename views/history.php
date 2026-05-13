<?php
session_start();
require_once '../database.php';

// 1. Capture Filter Inputs
$search_resource = $_GET['search_resource'] ?? '';
$search_user = $_GET['search_user'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// 2. Build Base SQL
$sql = "SELECT 
    DATE_FORMAT(h.Changed_At, '%b %d, %Y, %h:%i %p') AS 'Change_Date',
    r.Resource_Name,
    old_s.Status_Name AS 'From_Status',
    new_s.Status_Name AS 'To_Status',
    CONCAT(u.First_Name, ' ', u.Last_Name) AS 'Updated_By',
    res.Reservation_ID
FROM history h
LEFT JOIN resource r ON h.Resource_ID = r.Resource_ID
LEFT JOIN equipment_status old_s ON h.Old_Status = old_s.Status_ID
LEFT JOIN equipment_status new_s ON h.New_Status = new_s.Status_ID
LEFT JOIN reservation res ON h.Reservation_ID = res.Reservation_ID
LEFT JOIN users u ON res.Users_ID = u.Users_ID
WHERE u.Department_ID = ?";

$params = [$_SESSION['department']];
$types = "i";

// 3. Dynamic Filters (Matching logic from reservations)
if (!empty($search_user)) {
    $sql .= " AND CONCAT(u.First_Name, ' ', u.Last_Name) LIKE ?";
    $params[] = "%$search_user%";
    $types .= "s";
}
if (!empty($search_resource)) {
    $sql .= " AND r.Resource_Name LIKE ?";
    $params[] = "%$search_resource%";
    $types .= "s";
}
if (!empty($start_date)) {
    $sql .= " AND DATE(h.Changed_At) >= ?";
    $params[] = $start_date;
    $types .= "s";
}
if (!empty($end_date)) {
    $sql .= " AND DATE(h.Changed_At) <= ?";
    $params[] = $end_date;
    $types .= "s";
}

$sql .= ' ORDER BY h.Changed_At DESC;';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$history = $stmt->get_result();
?>

<!-- 4. The UI Structure -->
<div class="reservation-container" style="flex: 1; display: flex; flex-direction: column;">
    <div class="table-controls">
        <!-- JS Section 8 & 10 targets this form -->
        <form class="filter-form" onsubmit="return false;">
            <input type="text" name="search_resource" placeholder="Search Resource..." value="<?= htmlspecialchars($search_resource) ?>">
            <input type="text" name="search_user" placeholder="Updated By..." value="<?= htmlspecialchars($search_user) ?>">

            <div class="date-group">
                <label>From:</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
            </div>

            <div class="date-group">
                <label>To:</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
            </div>

            <button type="button" class="reset-filters btn-reset">
                <span class="material-icons">restart_alt</span> Clear Filters
            </button>
        </form>
    </div>

    <div class="dashboard-containers">
        <!-- JS Section 8 updates the content inside this div -->
        <div class="resources" id="history">
            <table class="resource-table">
                <thead>
                    <tr>
                        <th>Change Date</th>
                        <th>Resource Name</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Updated By</th>
                        <th>Reservation ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($history->num_rows > 0): ?>
                        <?php while ($row = $history->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['Change_Date']) ?></td>
                                <td><?= htmlspecialchars($row['Resource_Name']) ?></td>
                                <td>
                                    <?php
                                    $s = strtolower($row['From_Status'] ?? 'no-status');
                                    $class = 'status-' . str_replace(' ', '-', $s);
                                    ?>
                                    <span class="status-badge <?= $class ?>-badge">
                                        <?= htmlspecialchars($row['From_Status'] ?? 'N/A') ?> ==>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $s = strtolower($row['To_Status'] ?? 'no-status');
                                    $class = 'status-' . str_replace(' ', '-', $s);
                                    ?>
                                    <span class="status-badge <?= $class ?>-badge">
                                        <?= htmlspecialchars($row['To_Status'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['Updated_By'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['Reservation_ID'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No history records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>