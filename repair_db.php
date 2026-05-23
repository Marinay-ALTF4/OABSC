<?php
$conn = new mysqli('localhost', 'root', '', 'OABSC');

// Repair all tables
$tables = $conn->query("SHOW TABLES")->fetch_all();
foreach ($tables as $t) {
    $table = $t[0];
    $result = $conn->query("REPAIR TABLE `$table`");
    $row = $result->fetch_assoc();
    echo "$table: " . ($row['Msg_text'] ?? 'done') . "\n";
}

$conn->close();
echo "\nDone!\n";
