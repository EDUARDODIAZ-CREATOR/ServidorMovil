<?php
// Obtener API Key desde variable de entorno (Clever Cloud)
$apiKey = getenv('SENDINBLUE_API_KEY');

$email = $_REQUEST['email'] ?? null;

if (!$email) {
    echo json_encode(["mensaje" => "No se recibió un correo."]);
    exit;
}

// Configuración de la base de datos
$servername = "bstvndf39q5wtuj2jq3c-mysql.services.clever-cloud.com";
$username   = "unrl4oy6t7svgk6i";
$password   = "E8cg8K1l4HKAMW7E1ndd";
$dbname     = "bstvndf39q5wtuj2jq3c";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("CALL sp_recuperar_contrasena(:email)");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        echo json_encode(["mensaje" => "Correo no registrado."]);
        exit;
    }

    $nombre     = $result['nombreUsuario'];
    $contrasena = $result['passwordLogin'];

    // Contenido del correo
    $subject = "Recuperación de contraseña - Sistema de Login";
    $textContent = "Hola $nombre,\n\nTu contraseña registrada en el sistema es: $contrasena\n\nPor favor, no compartas esta información con nadie.";

    // Remitente verificado en Sendinblue
    $senderEmail = getenv('SENDINBLUE_SENDER_EMAIL'); // ejemplo: tu-email-verificado@ejemplo.com
    $senderName  = "Soporte del Sistema";

    $data = [
        'sender' => ['name' => $senderName, 'email' => $senderEmail],
        'to' => [['email' => $email, 'name' => $nombre]],
        'subject' => $subject,
        'textContent' => $textContent
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.sendinblue.com/v3/smtp/email");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "api-key: $apiKey",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        echo json_encode(["mensaje" => "Error envío correo: $err"]);
    } else {
        $apiResponse = json_decode($response, true);
        echo json_encode([
            "mensaje" => "Correo enviado",
            "respuesta_API" => $apiResponse
        ]);
    }

} catch(PDOException $e) {
    echo json_encode(["mensaje" => "Error DB: " . $e->getMessage()]);
}

$conn = null;
?>