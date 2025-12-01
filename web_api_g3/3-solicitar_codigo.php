<?php
// ---- 3-solicitar_codigo.php ----
header("Content-Type: application/json");

// Importar las clases de PHPMailer (asumiendo que copiaste la carpeta 'src' de PHPMailer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$email = $_POST['email'] ?? '';

if (empty($email)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "mensaje" => "El correo es requerido."]);
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

    // 1. Verificar si el usuario existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // 2. Generar código y tiempo de expiración (10 minutos)
        $codigo = rand(100000, 999999);
        $expiracion = date('Y-m-d H:i:s', time() + 600); // 10 minutos desde ahora

        // 3. Guardar el código y la expiración en la BD
        $stmtUpdate = $conn->prepare("UPDATE usuarios SET codigo_recuperacion = :codigo, codigo_expiracion = :expiracion WHERE email = :email");
        $stmtUpdate->bindParam(':codigo', $codigo);
        $stmtUpdate->bindParam(':expiracion', $expiracion);
        $stmtUpdate->bindParam(':email', $email);
        $stmtUpdate->execute();

        // 4. Enviar el correo electrónico
        $mail = new PHPMailer(true);
        try {
            // ---- Configuración del Servidor de Correo (Gmail) ----
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            
            // ▼▼▼ ¡¡¡ATENCIÓN: USA TUS CREDENCIALES AQUÍ!!! ▼▼▼
            $mail->Username   = 'jeduardoad06@gmail.com';     // TU CORREO DE GMAIL
            $mail->Password   = 'mlmt xkso usxk gtqd';     // TU CONTRASEÑA DE APLICACIÓN DE 16 LETRAS

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // ---- Contenido del Correo ----
            $mail->setFrom('no-reply@miapp.com', 'Soporte de Mi App');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Tu Codigo de Verificacion';
            $mail->Body    = 'Hola,<br><br>Tu codigo para verificar tu identidad es: <b>' . $codigo . '</b><br>Este codigo expirara en 10 minutos.';

            $mail->send();
            http_response_code(200);
            echo json_encode(["status" => "ok", "mensaje" => "Se ha enviado un código de verificación a tu correo."]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "mensaje" => "No se pudo enviar el correo. Mailer Error: {$mail->ErrorInfo}"]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "mensaje" => "El correo electrónico no se encuentra registrado."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos: " . $e->getMessage()]);
}
?>
