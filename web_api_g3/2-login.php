<?php

// --- CABECERA ---
// Asegura que la respuesta siempre sea de tipo JSON.
header('Content-Type: application/json');
// Permite solicitudes desde cualquier origen (CORS). Ajusta si es necesario por seguridad.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Si la solicitud es de tipo OPTIONS (pre-vuelo CORS), termina la ejecución.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

// --- CONEXIÓN A LA BASE DE DATOS ---
// Reemplaza con tus propias credenciales de Clever Cloud.
$servername = "tu_servidor_clever_cloud";    // Ejemplo: bz2l2g30jwxqzqtnvrl2-mysql.services.clever-cloud.com
$username = "tu_usuario_clever_cloud";        // Ejemplo: udxjwxa62qayesb2
$password = "tu_contraseña_clever_cloud";   // Ejemplo: GvM352aCs2sA2pYwP1sL
$dbname = "tu_base_de_datos_clever_cloud"; // Ejemplo: bz2l2g30jwxqzqtnvrl2

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    // Si la conexión falla, envía un error JSON y termina.
    echo json_encode(["status" => "error", "mensaje" => "Error de conexión a la base de datos: " . $conn->connect_error]);
    exit();
}

// --- LÓGICA DE LOGIN ---

// 1. Recibir el JSON enviado desde la app Android.
$json = file_get_contents('php://input');
// Decodificar el JSON a un objeto PHP.
$data = json_decode($json);

// 2. Validar que los datos necesarios (email y password) existen.
if (!isset($data->email) || !isset($data->password)) {
    // Si faltan datos, envía un error JSON.
    echo json_encode(["status" => "error", "mensaje" => "Faltan datos de email o contraseña."]);
    exit();
}

// 3. Obtener los datos y limpiarlos (medida básica de seguridad).
$email = $data->email;
$password_ingresada = $data->password;

// 4. Usar SENTENCIAS PREPARADAS para evitar inyección SQL (MUY IMPORTANTE).
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
if ($stmt === false) {
    echo json_encode(["status" => "error", "mensaje" => "Error al preparar la consulta: " . $conn->error]);
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// 5. Verificar si se encontró un usuario.
if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    
    // 6. Verificar si la contraseña coincide con el hash almacenado.
    // Se asume que la contraseña en la BD está hasheada con password_hash().
    if (password_verify($password_ingresada, $usuario['password'])) {
        // --- ÉXITO ---
        // La contraseña es correcta. Envía una respuesta JSON de éxito con los datos del usuario.
        echo json_encode(["status" => "ok", "mensaje" => "Login exitoso", "usuario" => $usuario]);
    } else {
        // --- ERROR DE CONTRASEÑA ---
        // La contraseña es incorrecta. Envía una respuesta JSON de error.
        echo json_encode(["status" => "error", "mensaje" => "Contraseña incorrecta."]);
    }
} else {
    // --- ERROR DE USUARIO NO ENCONTRADO ---
    // No se encontró un usuario con ese email. Envía una respuesta JSON de error.
    echo json_encode(["status" => "error", "mensaje" => "El email no está registrado."]);
}

// 7. Cerrar la conexión.
$stmt->close();
$conn->close();

?>
