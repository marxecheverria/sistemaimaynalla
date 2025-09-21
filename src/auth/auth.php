<?php
session_start();

// Configurar respuesta JSON
header('Content-Type: application/json');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Obtener datos del formulario
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validaciones básicas
if (empty($username)) {
    echo json_encode(['success' => false, 'message' => 'El nombre de usuario es obligatorio']);
    exit();
}

if (empty($password)) {
    echo json_encode(['success' => false, 'message' => 'La contraseña es obligatoria']);
    exit();
}

if (strlen($username) < 3) {
    echo json_encode(['success' => false, 'message' => 'El usuario debe tener al menos 3 caracteres']);
    exit();
}

if (strlen($password) < 4) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 4 caracteres']);
    exit();
}

try {
    // Intentar conectar a la base de datos
    include '../../conexion.php';
    
    // Verificar si la conexión está disponible
    if (!isset($conn) || $conn === null || (is_object($conn) && isset($conn->connect_error))) {
        // Modo de desarrollo sin base de datos
        if ($username === 'admin' && $password === 'admin123') {
            // Crear sesión de desarrollo
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = 'admin';
            $_SESSION['nombre_completo'] = 'Administrador';
            $_SESSION['email'] = 'admin@sistema.com';
            $_SESSION['rol'] = 'admin';
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            $_SESSION['modo_desarrollo'] = true;
            
            echo json_encode([
                'success' => true, 
                'message' => 'Acceso exitoso (Modo Desarrollo)',
                'user' => [
                    'id' => 1,
                    'username' => 'admin',
                    'nombre_completo' => 'Administrador',
                    'rol' => 'admin'
                ],
                'desarrollo' => true
            ]);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos. Use admin/admin123 para modo desarrollo']);
            exit();
        }
    }
    
    // Modo producción con base de datos
    // Escapar datos para prevenir SQL injection
    $username_escaped = $conn->real_escape_string($username);
    
    // Consultar usuario en la base de datos
    $sql = "SELECT id, username, password, nombre_completo, email, activo, rol, fecha_creacion 
            FROM usuarios 
            WHERE username = ? AND activo = 'Si'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username_escaped);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Log del intento de acceso fallido
        error_log("Intento de acceso fallido - Usuario no encontrado: " . $username);
        
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
        exit();
    }
    
    $user = $result->fetch_assoc();
    
    // Verificar contraseña
    if (!password_verify($password, $user['password'])) {
        // Log del intento de acceso fallido
        error_log("Intento de acceso fallido - Contraseña incorrecta para usuario: " . $username);
        
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
        exit();
    }
    
    // Verificar si el usuario está activo
    if ($user['activo'] !== 'Si') {
        echo json_encode(['success' => false, 'message' => 'Su cuenta está desactivada. Contacte al administrador']);
        exit();
    }
    
    // Actualizar último acceso
    $update_sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user['id']);
    $update_stmt->execute();
    
    // Crear sesión
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nombre_completo'] = $user['nombre_completo'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['rol'] = $user['rol'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    
    // Log del acceso exitoso
    error_log("Acceso exitoso - Usuario: " . $username . " (ID: " . $user['id'] . ")");
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true, 
        'message' => 'Acceso exitoso',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'nombre_completo' => $user['nombre_completo'],
            'rol' => $user['rol']
        ]
    ]);
    
} catch (Exception $e) {
    // Log del error
    error_log("Error en autenticación: " . $e->getMessage());
    
    // Modo de desarrollo como fallback
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['nombre_completo'] = 'Administrador';
        $_SESSION['email'] = 'admin@sistema.com';
        $_SESSION['rol'] = 'admin';
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['modo_desarrollo'] = true;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Acceso exitoso (Modo Desarrollo - Error DB)',
            'user' => [
                'id' => 1,
                'username' => 'admin',
                'nombre_completo' => 'Administrador',
                'rol' => 'admin'
            ],
            'desarrollo' => true
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor. Use admin/admin123 para modo desarrollo']);
    }
} finally {
    if (isset($conn) && $conn !== null && !is_object($conn)) {
        $conn->close();
    }
}
?>

