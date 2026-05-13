<?php
session_start();
require_once '../database.php';
$isAdmin = ($_SESSION['role'] === 'admin');
// Added quotes around the query string
$sql = "SELECT r.Resource_ID, res.Resource_Name, c.Category_Name, s.Status_Name 
    FROM reservation r 
    LEFT JOIN resource res ON r.Resource_ID = res.Resource_ID
    LEFT JOIN categories c ON res.Categories_ID = c.Categories_ID 
    LEFT JOIN equipment_status s ON res.Status_ID = s.Status_ID 
    WHERE r.Department_ID = {$_SESSION['department']} 
    AND r.Users_ID = {$_SESSION['userid']}
    AND s.Status_ID IN (2, 3);
   
    ";
$resources = $conn->query($sql);

/* RETURNS TOTAL COUNT DEPENDING ON */
$statussql = "SELECT s.Status_Name, c.Category_Name, COUNT(*) AS Total 
        FROM resource r
        JOIN equipment_status s ON r.Status_ID = s.Status_ID
        JOIN categories c ON r.Categories_ID = c.Categories_ID
        WHERE r.Department_ID = {$_SESSION['department']}
        GROUP BY s.Status_Name, c.Category_Name";

$result = $conn->query($statussql);
$data = [];

while ($row = $result->fetch_assoc()) {
    // Structure: $data['Available']['Room'] = 5
    $data[$row['Status_Name']][$row['Category_Name']] = $row['Total'];
}

// Helper function to prevent "Undefined Index" errors if a count is 0
function getCount($status, $cat, $data)
{
    return $data[$status][$cat] ?? 0;
}

if ($isAdmin) {
    $historysql = "SELECT 
    DATE_FORMAT(h.Changed_At, '%b %d, %Y, %h:%i %p') AS 'Change Date',
    r.Resource_Name AS 'Resource Name',
    old_s.Status_Name AS 'From',
    new_s.Status_Name AS 'To',
    CONCAT(u.First_Name, ' ', u.Last_Name) AS 'Updated By',
    res.Reservation_ID AS 'Linked Reservation'
FROM history h
LEFT JOIN resource r ON h.Resource_ID = r.Resource_ID
LEFT JOIN equipment_status old_s ON h.Old_Status = old_s.Status_ID
LEFT JOIN equipment_status new_s ON h.New_Status = new_s.Status_ID
LEFT JOIN reservation res ON h.Reservation_ID = res.Reservation_ID
LEFT JOIN users u ON res.Users_ID = u.Users_ID
-- Filter by the department ID stored in the current session
WHERE u.Department_ID = {$_SESSION['department']}  
ORDER BY h.Changed_At DESC
LIMIT 10;";
} else {
    $historysql = "SELECT 
    DATE_FORMAT(h.Changed_At, '%b %d, %Y, %h:%i %p') AS 'Change Date',
    r.Resource_Name AS 'Resource Name',
    old_s.Status_Name AS 'From',
    new_s.Status_Name AS 'To',
    CONCAT(u.First_Name, ' ', u.Last_Name) AS 'Updated By',
    res.Reservation_ID AS 'Linked Reservation'
    FROM history h
    LEFT JOIN resource r ON h.Resource_ID = r.Resource_ID
    LEFT JOIN equipment_status old_s ON h.Old_Status = old_s.Status_ID
    LEFT JOIN equipment_status new_s ON h.New_Status = new_s.Status_ID
    LEFT JOIN reservation res ON h.Reservation_ID = res.Reservation_ID
    LEFT JOIN users u ON res.Users_ID = u.Users_ID
    WHERE 
        u.Users_ID = {$_SESSION['userid']} 
        AND u.Department_ID = {$_SESSION['department']}  
    ORDER BY h.Changed_At DESC
    LIMIT 10;";
}

$history = $conn->query($historysql);


// ... (Your existing $sql and $statussql logic stays the same) ...

// --- NEW RESERVATIONS LOGIC ---
$searchUser = $_GET['search_user'] ?? '';
$searchResource = $_GET['search_resource'] ?? '';
$statusFilter = $_GET['status_filter'] ?? '';

$reservationsSql = "SELECT 
    ra.Reservation_ID, 
    CONCAT(u.First_Name, ' ', u.Last_Name) AS Full_Name,
    rb.Resource_Name, 
    ra.Reservation_Date, 
    ra.Return_Date
FROM reservation ra
LEFT JOIN users u ON ra.Users_ID = u.Users_ID
LEFT JOIN resource rb ON ra.Resource_ID = rb.Resource_ID
WHERE ra.Department_ID = {$_SESSION['department']}";

// Filter for non-admins
if (!$isAdmin) {
    $reservationsSql .= " AND ra.Users_ID = {$_SESSION['userid']}";
}

// Add simple text filters if they are set in the URL
if (!empty($searchUser)) {
    $reservationsSql .= " AND CONCAT(u.First_Name, ' ', u.Last_Name) LIKE '%" . $conn->real_escape_string($searchUser) . "%'";
}
if ($statusFilter === 'active') {
    $reservationsSql .= " AND ra.Return_Date >= NOW()";
} elseif ($statusFilter === 'expired') {
    $reservationsSql .= " AND ra.Return_Date < NOW()";
}

$reservationsSql .= " ORDER BY ra.Reservation_Date DESC LIMIT 10";
$reservations = $conn->query($reservationsSql);


?>

<div class="dashboard-containers">
    <div class="insights">
        <div class="card clickable" data-status="1">
            <div id="stickycontent" class="card-top">
                <span id="available" class="material-icons">check_circle</span>
                <h2>Available</h2>
            </div>
            <div class="card-content">
                <div class="amount-label">
                    <h3>Room </span></h3>
                    <h3>Projector </h3>
                    <h3>Laptop</h3>
                    <h3>Laboratory Equipment</h3>
                    <h3>Smart TV</h3>
                </div>
                <div class="amount">
                    <span class="text-muted"><?php echo getCount('Available', 'Room', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('Available', 'Projector', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('Available', 'Laptop', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('Available', 'Laboratory Equipment', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('Available', 'Smart TV', $data); ?></span>
                </div>
            </div>
        </div>
        <div class="card clickable" data-status="3">
            <div id="stickycontent" class="card-top">
                <span id="in-use" class="material-icons">do_disturb_on</span>
                <h2>In Use</h2>
            </div>
            <div class="card-content">
                <div class="amount-label">
                    <h3>Room </span></h3>
                    <h3>Projector </h3>
                    <h3>Laptop</h3>
                    <h3>Laboratory Equipment</h3>
                    <h3>Smart TV</h3>
                </div>
                <div class="amount">
                    <span class="text-muted"><?php echo getCount('In Use', 'Room', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('In Use', 'Projector', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('In Use', 'Laptop', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('In Use', 'Laboratory Equipment', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('In Use', 'Smart TV', $data); ?></span>
                </div>
            </div>
        </div>
        <div class="card clickable" data-status="2">
            <div id="stickycontent" class="card-top">
                <span id="reserved" class="material-icons">watch_later</span>
                <h2>Reserved</h2>
            </div>
            <div class="card-content">
                <div class="amount-label">
                    <h3>Room </span></h3>
                    <h3>Projector </h3>
                    <h3>Laptop</h3>
                    <h3>Laboratory Equipment</h3>
                    <h3>Smart TV</h3>
                </div>
                <div class="amount">
                    <span class="text-muted"><?php echo getCount('Reserved', 'Room', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('Reserved', 'Projector', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('Reserved', 'Laptop', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('Reserved', 'Laboratory Equipment', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('Reserved', 'Smart TV', $data); ?></span>
                </div>
            </div>
        </div>
        <div class="card clickable" data-status="4">
            <div id="stickycontent" class="card-top">
                <span id="under-maintenance" class="material-icons">error</span>
                <h2>Under Maintenance</h2>
            </div>
            <div class="card-content">
                <div class="amount-label">
                    <h3>Room </span></h3>
                    <h3>Projector </h3>
                    <h3>Laptop</h3>
                    <h3>Laboratory Equipment</h3>
                    <h3>Smart TV</h3>
                </div>
                <div class="amount">
                    <span class="text-muted"><?php echo getCount('Under Maintenance', 'Room', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('Under Maintenance', 'Projector', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('Under Maintenance', 'Laptop', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('Under Maintenance', 'Laboratory Equipment', $data); ?></span>
                    <span class="text-muted"><?php echo getCount('Under Maintenance', 'Smart TV', $data); ?></span>
                </div>
            </div>
        </div>

    </div>
    <div class="dashboard-info">
        <?php if ($isAdmin): ?>
            <div class="container-1" style="flex: 2;">
                <h2 class="title">Faculty Reservations</h2>
                <div id="dashboard" class="resources">
                    <?php if ($reservations && $reservations->num_rows > 0): ?>
                        <table class="resource-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Resource</th>
                                    <th>Schedule (From - To)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($res = $reservations->fetch_assoc()):
                                    $start = new DateTime($res['Reservation_Date']);
                                    $end = new DateTime($res['Return_Date']);
                                    $now = new DateTime();

                                    $isExpired = $now > $end;
                                    $statusLabel = $isExpired ? 'Expired' : 'Active';
                                    $statusClass = $isExpired ? 'status-under-maintenance-badge' : 'status-available-badge';
                                    $isSameDay = $start->format('Y-m-d') === $end->format('Y-m-d');
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($res['Reservation_ID']) ?></td>
                                        <td><?= htmlspecialchars($res['Full_Name']) ?></td>
                                        <td><?= htmlspecialchars($res['Resource_Name']) ?></td>
                                        <td>
                                            <?= $start->format('M j, g:i A') ?> -
                                            <?= $isSameDay ? $end->format('g:i A') : $end->format('M j, g:i A') ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?= $statusClass ?>">
                                                <?= $statusLabel ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="padding: 20px;">No recent reservations found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="container-1" style="flex: 1; min-width: 550px;">
                <h2 class="title">Used Resources</h2>
                <div id="dashboard" class="resources">
                    <?php if ($resources && $resources->num_rows > 0): ?>
                        <table class="resource-table">
                            <thead>
                                <tr>
                                    <th>Resource ID</th>
                                    <th>Resource Name</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Action</th> <!-- Added Action Column -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($resource = $resources->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($resource['Resource_ID']) ?></td>
                                        <td><?= htmlspecialchars($resource['Resource_Name']) ?></td>
                                        <td><?= htmlspecialchars($resource['Category_Name'] ?? 'No Category') ?></td>
                                        <td><?= htmlspecialchars($resource['Status_Name'] ?? 'No Status') ?></td>
                                        <td>
                                            <?php if (($resource['Status_Name'] ?? '') === 'In Use'): ?>
                                                <!-- RETURN BUTTON -->
                                                <form action="views/return_resource.php" method="POST" class="ajax-form">
                                                    <input type="hidden" name="resource_id" value="<?= htmlspecialchars($resource['Resource_ID']) ?>">
                                                    <button type="submit" class="btn-return">Return</button>
                                                </form>

                                            <?php elseif (($resource['Status_Name'] ?? '') === 'Reserved'): ?>
                                                <!-- USE BUTTON -->
                                                <form action="views/use_resource.php" method="POST" class="ajax-form">
                                                    <input type="hidden" name="resource_id" value="<?= htmlspecialchars($resource['Resource_ID']) ?>">
                                                    <button type="submit" class="btn-use">Use</button>
                                                </form>

                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
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
        <?php else: ?>
            <div class="container-1" style="flex: 2;">
                <h2 class="title">Reservation History</h2>
                <div id="dashboard" class="resources">
                    <?php if ($reservations && $reservations->num_rows > 0): ?>
                        <table class="resource-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Resource</th>
                                    <th>Schedule (From - To)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($res = $reservations->fetch_assoc()):
                                    $start = new DateTime($res['Reservation_Date']);
                                    $end = new DateTime($res['Return_Date']);
                                    $now = new DateTime();

                                    $isExpired = $now > $end;
                                    $statusLabel = $isExpired ? 'Expired' : 'Active';
                                    $statusClass = $isExpired ? 'status-under-maintenance-badge' : 'status-available-badge';
                                    $isSameDay = $start->format('Y-m-d') === $end->format('Y-m-d');
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($res['Reservation_ID']) ?></td>
                                        <td><?= htmlspecialchars($res['Full_Name']) ?></td>
                                        <td><?= htmlspecialchars($res['Resource_Name']) ?></td>
                                        <td>
                                            <?= $start->format('M j, g:i A') ?> -
                                            <?= $isSameDay ? $end->format('g:i A') : $end->format('M j, g:i A') ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?= $statusClass ?>">
                                                <?= $statusLabel ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="padding: 20px;">No recent reservations found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="container-1" style="flex: 1; min-width: 550px;">
                <h2 class="title">Used Resources</h2>
                <div id="dashboard" class="resources">
                    <?php if ($resources && $resources->num_rows > 0): ?>
                        <table class="resource-table">
                            <thead>
                                <tr>
                                    <th>Resource ID</th>
                                    <th>Resource Name</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Action</th> <!-- Added Action Column -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($resource = $resources->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($resource['Resource_ID']) ?></td>
                                        <td><?= htmlspecialchars($resource['Resource_Name']) ?></td>
                                        <td><?= htmlspecialchars($resource['Category_Name'] ?? 'No Category') ?></td>
                                        <td><?= htmlspecialchars($resource['Status_Name'] ?? 'No Status') ?></td>
                                        <td>
                                            <?php if (($resource['Status_Name'] ?? '') === 'In Use'): ?>
                                                <!-- RETURN BUTTON -->
                                                <form action="views/return_resource.php" method="POST" class="ajax-form">
                                                    <input type="hidden" name="resource_id" value="<?= htmlspecialchars($resource['Resource_ID']) ?>">
                                                    <button type="submit" class="btn-return">Return</button>
                                                </form>

                                            <?php elseif (($resource['Status_Name'] ?? '') === 'Reserved'): ?>
                                                <!-- USE BUTTON -->
                                                <form action="views/use_resource.php" method="POST" class="ajax-form">
                                                    <input type="hidden" name="resource_id" value="<?= htmlspecialchars($resource['Resource_ID']) ?>">
                                                    <button type="submit" class="btn-use">Use</button>
                                                </form>

                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
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
        <?php endif; ?>

    </div>
</div>