<?php
session_start();
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
include 'db.php';

$notifications = [];

$pendingOrders = mysqli_query($conn, "
    SELECT o.order_id, c.full_name, o.created_at,
           p.product_name
    FROM orders o
    INNER JOIN customers c ON o.user_id = c.user_id
    LEFT JOIN order_details od ON o.order_id = od.order_id
    LEFT JOIN product p ON od.product_id = p.product_id
    WHERE o.status = 'Pending'
    ORDER BY o.created_at DESC
    LIMIT 10
");
while ($row = mysqli_fetch_assoc($pendingOrders)) {
    $notifications[] = [
        'type'    => 'order',
        'icon'    => '🛒',
        'title'   => 'New Order #' . $row['order_id'],
        'message' => htmlspecialchars($row['full_name']) . ' — ' . htmlspecialchars($row['product_name'] ?? 'Unknown Product'),
        'time'    => $row['created_at'],
        'link'    => 'adminOrders.php',
    ];
}
$repairResult = mysqli_query($conn, "
    SELECT id, first_name, last_name, contact, date, branch, problem, submitted_at
    FROM repair_schedules
    WHERE date >= CURDATE()
       OR submitted_at >= NOW() - INTERVAL 7 DAY
    ORDER BY date ASC
    LIMIT 10
");
if ($repairResult) {
    while ($row = mysqli_fetch_assoc($repairResult)) {
        $fullName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
        $notifications[] = [
            'type'    => 'repair',
            'icon'    => '🔧',
            'title'   => 'Repair Schedule #' . $row['id'],
            'message' => $fullName . ' — ' . $row['branch'] . ' on ' . date('M d, Y', strtotime($row['date'])),
            'time'    => $row['submitted_at'],
            'link'    => 'adminReports.php',
        ];
    }
}
$maintResult = mysqli_query($conn, "
    SELECT id, first_name, last_name, contact, date, branch, description, submitted_at
    FROM maintenance_schedules
    WHERE date >= CURDATE()
       OR submitted_at >= NOW() - INTERVAL 7 DAY
    ORDER BY date ASC
    LIMIT 10
");
if ($maintResult) {
    while ($row = mysqli_fetch_assoc($maintResult)) {
        $fullName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
        $notifications[] = [
            'type'    => 'maintenance',
            'icon'    => '⚙️',
            'title'   => 'Maintenance Schedule #' . $row['id'],
            'message' => $fullName . ' — ' . $row['branch'] . ' on ' . date('M d, Y', strtotime($row['date'])),
            'time'    => $row['submitted_at'],
            'link'    => 'adminReports.php',
        ];
    }
}
usort($notifications, fn($a, $b) => strtotime($b['time']) - strtotime($a['time']));

header('Content-Type: application/json');
echo json_encode([
    'count'         => count($notifications),
    'notifications' => $notifications,
]);