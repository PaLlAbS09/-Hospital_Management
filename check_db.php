<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=hospital_management', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->query('SHOW TABLES');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Tables_in_hospital_management'] . "\n";
}