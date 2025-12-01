<?php
header("Content-Type: application/json");

// Obtener datos enviados por POST
$nombre     = $_POST['nombre'] ?? '';
$email      = $_POST['email'] ?? '';
$contrasena = $_POST['contraseña'] ?? '';

if (empty($nombre) || empty($email) || empty($contrasena)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "mensaje" => "Todos los campos son requeridos."]);
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

    // Verificar si el correo ya existe
    $check = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
    $check->bindParam(':email', $email);
    $check->execute();
    $existe = $check->fetchColumn();

    if ($existe > 0) {
        echo json_encode(["status" => "error", "mensaje" => "El correo ya está registrado."]);
        exit();
    }

    // Insertar usuario (contraseña sin encriptar - modo inseguro)
    $stmt = $conn->prepare(
        "INSERT INTO usuarios (nombre, email, contraseña)
         VALUES (:nombre, :email, :contrasena)"
    );

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':contrasena', $contrasena);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["status" => "ok", "mensaje" => "Usuario registrado correctamente."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "mensaje" => "Error al registrar."]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "mensaje" => "Error de BD: " . $e->getMessage()]);
} finally {
    $conn = null;
}
?>
