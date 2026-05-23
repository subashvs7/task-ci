<?php
define('BASEPATH', '1');
require 'application/config/database.php';
$conn = new mysqli('127.0.0.1', $db['default']['username'], $db['default']['password'], $db['default']['database']);
$r = $conn->query("DESCRIBE tm_tasks");
while($row = $r->fetch_assoc()) { print_r($row); }
