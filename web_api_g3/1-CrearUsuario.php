<?php

$nombreU     = $_REQUEST['nombreU'] ?? null;
$email       = $_REQUEST['email'] ?? null;
$contraseñaL = $_REQUEST['contraseñaL'] ?? null;

// Validación básica
if (!$nombreU || !$email || !$contraseñaL) {
    echo json_encode(["resultado" => "FALTAN_DATOS"]);
    exit;
}

// Datos de Clever Cloud
$servername = "blsc1i1tuhdeg2ueca1k-mysql.services.clever-cloud.com";
$username   = "uuelw8kcqhbqjfui";
$password   = "UvDUL8x7TRNjFO8UyRi2";
$dbname     = "blsc1i1tuhdeg2ueca1k";

try {
    $conn = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4;port=3306",
        $username,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Llamar SP
    $stmt = $conn->prepare("CALL sp_crear_usuario(:nom, :mail, :pass)");

    $stmt->bindParam(':nom',  $nombreU);
    $stmt->bindParam(':mail', $email);
    $stmt->bindParam(':pass', $contraseñaL);

    $stmt->execute();

    // Respuesta del SP
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($result);

} catch(PDOException $e) {
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}

$conn = null;
?>
