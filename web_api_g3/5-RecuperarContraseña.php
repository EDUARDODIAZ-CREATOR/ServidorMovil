<?php
header('Content-Type: application/json; charset=utf-8');

// =============================
//  Variables de entorno (Clever Cloud)
// =============================
$apiKey      = getenv('SENDINBLUE_API_KEY');
$senderEmail = getenv('SENDINBLUE_SENDER_EMAIL');
$senderName  = "Soporte del Sistema";

// Validar correo recibido
$email = $_REQUEST['email'] ?? null;

if (!$email) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "No se recibió un correo."
    ]);
    exit;
}

// =============================
//  Configuración BD (TU BD real)
// =============================
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

    // =============================
    //  Llamar SP real
    // =============================
    $stmt = $conn->prepare("CALL sp_recuperar_contrasena(:email)");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Cerrar cursor del SP (muy importante en Clever Cloud)
    $stmt->closeCursor();

    if (!$result || $result['nombreUsuario'] === null) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Correo no registrado."
        ]);
        exit;
    }

    $nombre     = $result['nombreUsuario'];
    $contrasena = $result['passwordLogin'];

    // =============================
    //  Construcción del correo
    // =============================
    $subject = "Recuperación de contraseña - Sistema de Login";

    $textContent = "Hola $nombre,

Tu contraseña registrada es: $contrasena

Por favor no compartas esta información.";

    // Datos para la API de Sendinblue
    $data = [
        "sender" => ["name" => $senderName, "email" => $senderEmail],
        "to" => [
            ["email" => $email, "name" => $nombre]
        ],
        "subject" => $subject,
        "textContent" => $textContent
    ];

    // =============================
    //  Enviar correo por API Sendinblue
    // =============================
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.sendinblue.com/v3/smtp/email",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "api-key: $apiKey",
            "Content-Type: application/json",
            "Accept: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    // =============================
    //  Respuesta JSON para tu App
    // =============================
    if ($err) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al enviar correo: $err"
        ]);
    } else {
        echo json_encode([
            "status" => "ok",
            "mensaje" => "Correo enviado correctamente.",
            "usuario" => [
                "nombre" => $nombre,
                "password" => $contrasena
            ]
        ]);
    }

} catch (PDOException $e) {

    echo json_encode([
        "status" => "error",
        "mensaje" => "Error DB: " . $e->getMessage()
    ]);

}

$conn = null;
?>
