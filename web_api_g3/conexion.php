<?php
$host = "blsc1i1tuhdeg2ueca1k-mysql.services.clever-cloud.com";
$dbname = "blsc1i1tuhdeg2ueca1k";
$username = "uuelw8kcqhbqjfui";
$password = "UvDUL8x7TRNjFO8UyRi2";
$port = "3306";

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode([
        "error" => "Error de conexión: ".$e->getMessage()
    ]);
    exit;
}
?>
