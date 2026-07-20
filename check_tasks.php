<?php
header('Content-Type: text/plain');

$conn = mysqli_connect("localhost", "zazu", "zazu@123", "task_manager_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Current PHP Time: " . date('Y-m-d H:i:s') . "\n";
echo "Current MySQL Time: ";
$q = mysqli_query($conn, "SELECT NOW()");
$r = mysqli_fetch_row($q);
echo $r[0] . "\n\n";

echo "Columns in tm_tasks:\n";
$res = mysqli_query($conn, "DESCRIBE tm_tasks");
while ($row = mysqli_fetch_assoc($res)) {
    echo "{$row['Field']} - {$row['Type']}\n";
}

mysqli_close($conn);




