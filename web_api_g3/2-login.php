<?php
header("Content-Type: application/json");

$email      = $_POST['email'] ?? '';
$contrasena = $_POST['contraseña'] ?? ''; 

if (empty($email) || empty($contrasena)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "mensaje" => "Email y contraseña son requeridos."]);
    exit();
}

// ----------------------------
// CONEXIÓN A CLEVER CLOUD
// ----------------------------
$servername = "blsc1i1tuhdeg2ueca1k-mysql.services.clever-cloud.com";
$username   = "uuelw8kcqhbqjfui";
$password   = "UvDUL8x7TRNjFO8UyRi2";
$dbname     = "blsc1i1tuhdeg2ueca1k";

try {
    $conn = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
        $username, 
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar al usuario por email
    $stmt = $conn->prepare(
        "SELECT id, nombre, email, contraseña 
         FROM usuarios 
         WHERE email = :email"
    );
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Comparación directa (modo inseguro)
    if ($user && $contrasena === $user['contraseña']) {
        http_response_code(200);
        echo json_encode([
            "status" => "ok",
            "mensaje" => "Inicio de sesión correcto (modo inseguro).",
            "usuario" => [
                "id" => (int)$user['id'],
                "nombre" => $user['nombre'],
                "email" => $user['email']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "mensaje" => "Email o contraseña incorrectos."]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "mensaje" => "Error de BD: " . $e->getMessage()]);
} finally {
    $conn = null;
}
?>
