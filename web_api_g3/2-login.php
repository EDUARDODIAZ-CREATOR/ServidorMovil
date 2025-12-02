<?php 
header('Content-Type: application/json; charset=utf-8');

// Recibir parámetros
$email = $_REQUEST['email'] ?? null;
$contraseña = $_REQUEST['contraseña'] ?? null;

// Validación básica
if (!$email || !$contraseña) {
    echo json_encode([
        "resultado" => "FALTAN_DATOS"
    ]);
    exit;
}

// DATOS DE CLEVER CLOUD (TU HOST NUEVO)
$servername = "blsc1i1tuhdeg2ueca1k-mysql.services.clever-cloud.com";
$username   = "uuelw8kcqhbqjfui";
$password   = "UvDUL8x7TRNjFO8UyRi2";
$dbname     = "blsc1i1tuhdeg2ueca1k";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4;port=3306", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // LLAMAR TU SP REAL: sp_login
    $stmt = $conn->prepare("CALL sp_login(:email, :contrasena)");

    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':contrasena', $contraseña);
    $stmt->execute();

    // SP login regresa una fila
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($result);

} catch(PDOException $e) {
    echo json_encode([
        "resultado" => "ERROR",
        "message" => $e->getMessage()
    ]);
}

$conn = null;
?>
