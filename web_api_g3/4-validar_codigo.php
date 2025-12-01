<?php
// ---- 4-validar_codigo.php ----
header("Content-Type: application/json");

$email = $_POST['email'] ?? '';
$codigo = $_POST['codigo'] ?? '';

if (empty($email) || empty($codigo)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "mensaje" => "Email y código son requeridos."]);
    exit();
}

// --- Configuración de la Base de Datos ---
$servername = "localhost";
$username = "root";
$password = "123456"; // TU CONTRASEÑA de XAMPP/MySQL
$dbname = "app_usuarios";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Llamamos a nuestro procedimiento almacenado
    $stmt = $conn->prepare("CALL sp_validar_codigo_y_recuperar(:email, :codigo)");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':codigo', $codigo);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Verificamos el resultado que nos dio el procedimiento
    if ($result && isset($result['resultado']) && $result['resultado'] == 'OK') {
        // Devolvemos los datos del usuario que entregó el procedimiento
        http_response_code(200);
        echo json_encode([
            "status" => "ok",
            "mensaje" => "Código verificado correctamente.",
            "usuario" => [
                "nombre" => $result['nombre'],
                "email" => $result['email'],
                "contrasena" => $result['contraseña']
            ]
        ]);
    } else {
        // El procedimiento nos dijo que el código era inválido o expirado
        http_response_code(400);
        echo json_encode(["status" => "error", "mensaje" => "Código inválido o expirado."]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos: " . $e->getMessage()]);
}
?>
