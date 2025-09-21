<?php
session_start();
include '../../verificar_sesion.php';
include '../../conexion.php';

// Verificar que el usuario tenga permisos de administrador
if ($_SESSION['rol'] !== 'admin') {
    header("Location: panel.php?error=sin_permisos");
    exit();
}

// Variables para mensajes
$message = '';
$message_type = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'crear':
                $username = trim($_POST['username']);
                $password = $_POST['password'];
                $nombre_completo = trim($_POST['nombre_completo']);
                $email = trim($_POST['email']);
                $rol = $_POST['rol'];
                
                // Validaciones
                if (empty($username) || empty($password) || empty($nombre_completo)) {
                    throw new Exception('Todos los campos obligatorios deben ser completados');
                }
                
                if (strlen($username) < 3) {
                    throw new Exception('El nombre de usuario debe tener al menos 3 caracteres');
                }
                
                if (strlen($password) < 4) {
                    throw new Exception('La contraseña debe tener al menos 4 caracteres');
                }
                
                // Verificar si el usuario ya existe
                $check_sql = "SELECT id FROM usuarios WHERE username = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("s", $username);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    throw new Exception('El nombre de usuario ya existe');
                }
                
                // Crear usuario
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO usuarios (username, password, nombre_completo, email, rol, activo) 
                        VALUES (?, ?, ?, ?, ?, 'Si')";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $username, $password_hash, $nombre_completo, $email, $rol);
                
                if ($stmt->execute()) {
                    $message = '✅ Usuario creado exitosamente';
                    $message_type = 'success';
                } else {
                    throw new Exception('Error al crear el usuario: ' . $stmt->error);
                }
                break;
                
            case 'editar':
                $id = intval($_POST['id']);
                $username = trim($_POST['username']);
                $nombre_completo = trim($_POST['nombre_completo']);
                $email = trim($_POST['email']);
                $rol = $_POST['rol'];
                $activo = $_POST['activo'];
                
                // Validaciones
                if (empty($username) || empty($nombre_completo)) {
                    throw new Exception('Todos los campos obligatorios deben ser completados');
                }
                
                // Verificar si el usuario ya existe (excluyendo el actual)
                $check_sql = "SELECT id FROM usuarios WHERE username = ? AND id != ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("si", $username, $id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    throw new Exception('El nombre de usuario ya existe');
                }
                
                // Actualizar usuario
                $sql = "UPDATE usuarios SET username = ?, nombre_completo = ?, email = ?, rol = ?, activo = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssi", $username, $nombre_completo, $email, $rol, $activo, $id);
                
                if ($stmt->execute()) {
                    $message = '✅ Usuario actualizado exitosamente';
                    $message_type = 'success';
                } else {
                    throw new Exception('Error al actualizar el usuario: ' . $stmt->error);
                }
                break;
                
            case 'eliminar':
                $id = intval($_POST['id']);
                
                if ($id == $_SESSION['user_id']) {
                    throw new Exception('No puedes eliminar tu propio usuario');
                }
                
                $sql = "DELETE FROM usuarios WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $message = '✅ Usuario eliminado exitosamente';
                    $message_type = 'success';
                } else {
                    throw new Exception('Error al eliminar el usuario: ' . $stmt->error);
                }
                break;
                
            case 'cambiar_password':
                $id = intval($_POST['id']);
                $nueva_password = $_POST['nueva_password'];
                
                if (strlen($nueva_password) < 4) {
                    throw new Exception('La contraseña debe tener al menos 4 caracteres');
                }
                
                $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $password_hash, $id);
                
                if ($stmt->execute()) {
                    $message = '✅ Contraseña actualizada exitosamente';
                    $message_type = 'success';
                } else {
                    throw new Exception('Error al actualizar la contraseña: ' . $stmt->error);
                }
                break;
        }
    } catch (Exception $e) {
        $message = '❌ ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Obtener lista de usuarios
$sql = "SELECT id, username, nombre_completo, email, rol, activo, fecha_creacion, ultimo_acceso 
        FROM usuarios ORDER BY fecha_creacion DESC";
$result = $conn->query($sql);
$usuarios = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sistema de Registro</title>
    <link rel="stylesheet" href="menu.css">
    <link rel="stylesheet" href="estilos-corporativos.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 130px 20px 40px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(28, 44, 80, 0.3);
        }
        
        .page-title {
            font-size: 2.5em;
            font-weight: 700;
            margin: 0 0 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .page-subtitle {
            font-size: 1.2em;
            opacity: 0.9;
            font-weight: 400;
        }
        
        .management-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .form-section {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(28, 44, 80, 0.1);
            border-left: 5px solid #dda619;
            display: none;
            animation: slideDown 0.3s ease-out;
        }
        
        .form-section.show {
            display: block;
        }
        
        .users-table {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(28, 44, 80, 0.1);
            overflow: hidden;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .table-title {
            font-size: 1.5em;
            font-weight: 600;
            color: #1c2c50;
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .table-icon {
            margin-right: 10px;
            font-size: 1.2em;
        }
        
        .btn-new-user {
            background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9em;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-new-user:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(28, 44, 80, 0.3);
        }
        
        .btn-cancel {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9em;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }
        
        .form-section.hide {
            animation: slideUp 0.3s ease-out;
            display: none;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1c2c50;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-input, .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #fff;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }
        
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #dda619;
            box-shadow: 0 0 0 3px rgba(221, 166, 25, 0.1);
        }
        
        .form-input.error {
            border-color: #a5221c;
            box-shadow: 0 0 0 3px rgba(165, 34, 28, 0.1);
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            margin: 5px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9em;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(28, 44, 80, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #dda619 0%, #c49a0f 100%);
            color: white;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(221, 166, 25, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #a5221c 0%, #8a1c16 100%);
            color: white;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(165, 34, 28, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
        }
        
        .users-table {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(28, 44, 80, 0.1);
            overflow: hidden;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th {
            background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9em;
        }
        
        .table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        
        .table tr:hover {
            background: rgba(221, 166, 25, 0.05);
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-active {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .status-inactive {
            background: linear-gradient(135deg, #a5221c 0%, #8a1c16 100%);
            color: white;
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .role-admin {
            background: linear-gradient(135deg, #dda619 0%, #c49a0f 100%);
            color: white;
        }
        
        .role-usuario {
            background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%);
            color: white;
        }
        
        .role-supervisor {
            background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
            color: white;
        }
        
        .actions {
            display: flex;
            gap: 5px;
            flex-wrap: nowrap;
            justify-content: flex-start;
            align-items: center;
            min-width: 280px;
        }
        
        .actions .btn {
            padding: 6px 10px;
            font-size: 0.75em;
            margin: 0;
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        /* Asegurar que la columna Acciones tenga suficiente ancho */
        .table th:nth-child(6),
        .table td:nth-child(6) {
            min-width: 300px;
            width: 300px;
        }
        
        .message {
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .message.success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
            border-left: 4px solid #28a745;
        }
        
        .message.error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #a5221c;
            border: 1px solid #f5c6cb;
            border-left: 4px solid #a5221c;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            animation: fadeIn 0.3s ease-out;
        }
        
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            animation: slideInScale 0.3s ease-out;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .modal-title {
            font-size: 1.5em;
            font-weight: 600;
            color: #1c2c50;
            margin: 0;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #6c757d;
            transition: color 0.3s ease;
        }
        
        .close-btn:hover {
            color: #a5221c;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideInScale {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: 130px 10px 20px;
            }
            
            .management-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .page-title {
                font-size: 2em;
            }
            
            .table {
                font-size: 0.8em;
            }
            
            .table th, .table td {
                padding: 8px 6px;
            }
            
            .actions {
                flex-direction: row;
                flex-wrap: wrap;
                min-width: 250px;
            }
            
            .actions .btn {
                padding: 5px 8px;
                font-size: 0.7em;
                margin: 1px;
                flex: 1;
                min-width: 70px;
            }
        }
        
        @media (max-width: 480px) {
            .page-title {
                font-size: 1.6em;
            }
            
            .form-section, .users-table {
                padding: 20px;
            }
            
            .table th, .table td {
                padding: 6px 4px;
                font-size: 0.7em;
            }
            
            /* En pantallas muy pequeñas, mantener los botones en una fila pero más compactos */
            .actions {
                min-width: 200px;
                gap: 2px;
            }
            
            .actions .btn {
                padding: 4px 6px;
                font-size: 0.65em;
                min-width: 60px;
            }
        }
    </style>
</head>
<body>
    <!-- Menú de navegación profesional -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="panel.php" class="nav-logo">
                <img src="imagenes/logo.png" alt="Logo del Sistema" class="logo-icon">
            </a>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="registro.html" class="nav-link">
                        <span class="icon">📝</span> Nuevo Registro
                    </a>
                </li>
                <li class="nav-item">
                    <a href="panel.php" class="nav-link">
                        <span class="icon">📋</span> Panel Principal
                    </a>
                </li>
                <li class="nav-item">
                    <a href="crear_usuarios.php" class="nav-link active">
                        <span class="icon">👥</span> Gestión Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <span class="icon">🚪</span> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">👥 Gestión de Usuarios</h1>
            <p class="page-subtitle">Administración del Sistema de Registro de Niños</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <div class="management-container">
            <!-- Lista de Usuarios -->
            <div class="users-table">
                <div class="table-header">
                    <h2 class="table-title">
                        <span class="table-icon">👥</span>
                        Usuarios del Sistema
                    </h2>
                    <button class="btn-new-user" onclick="showNewUserForm()">
                        <span>➕</span> Nuevo Usuario
                    </button>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($usuario['username']) ?></strong></td>
                                <td><?= htmlspecialchars($usuario['nombre_completo']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td>
                                    <span class="role-badge role-<?= $usuario['rol'] ?>">
                                        <?= ucfirst($usuario['rol']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($usuario['activo']) ?>">
                                        <?= $usuario['activo'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <button class="btn btn-secondary" onclick="editUser(<?= $usuario['id'] ?>)">
                                            ✏️ Editar
                                        </button>
                                        <button class="btn btn-success" onclick="changePassword(<?= $usuario['id'] ?>)">
                                            🔒 Cambiar Pass
                                        </button>
                                        <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                        <button class="btn btn-danger" onclick="deleteUser(<?= $usuario['id'] ?>)">
                                            🗑️ Eliminar
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Formulario de Creación/Edición -->
            <div class="form-section" id="formSection">
                <div class="table-header">
                    <h2 class="table-title">
                        <span class="table-icon">✏️</span>
                        <span id="form-title">Crear Nuevo Usuario</span>
                    </h2>
                    <button class="btn-cancel" onclick="hideForm()">
                        <span>❌</span> Cancelar
                    </button>
                </div>
                
                <form id="userForm" method="POST">
                    <input type="hidden" name="action" id="form-action" value="crear">
                    <input type="hidden" name="id" id="user-id" value="">
                    
                    <div class="form-group">
                        <label class="form-label" for="username">👤 Nombre de Usuario *</label>
                        <input type="text" id="username" name="username" class="form-input" required>
                    </div>
                    
                    <div class="form-group" id="password-group">
                        <label class="form-label" for="password">🔒 Contraseña *</label>
                        <input type="password" id="password" name="password" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="nombre_completo">📝 Nombre Completo *</label>
                        <input type="text" id="nombre_completo" name="nombre_completo" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">📧 Email</label>
                        <input type="email" id="email" name="email" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="rol">🎭 Rol *</label>
                        <select id="rol" name="rol" class="form-select" required>
                            <option value="usuario">Usuario</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="activo-group" style="display: none;">
                        <label class="form-label" for="activo">✅ Estado</label>
                        <select id="activo" name="activo" class="form-select">
                            <option value="Si">Activo</option>
                            <option value="No">Inactivo</option>
                        </select>
                    </div>
                    
                    <div style="text-align: center; margin-top: 25px;">
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <span id="submit-text">💾 Crear Usuario</span>
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            🔄 Limpiar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para cambiar contraseña -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">🔒 Cambiar Contraseña</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <form id="passwordForm" method="POST">
                <input type="hidden" name="action" value="cambiar_password">
                <input type="hidden" name="id" id="password-user-id">
                
                <div class="form-group">
                    <label class="form-label" for="nueva_password">🔒 Nueva Contraseña *</label>
                    <input type="password" id="nueva_password" name="nueva_password" class="form-input" required>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">💾 Actualizar Contraseña</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">❌ Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Datos de usuarios para JavaScript
        const usuarios = <?= json_encode($usuarios) ?>;
        
        // Función para mostrar el formulario de nuevo usuario
        function showNewUserForm() {
            const formSection = document.getElementById('formSection');
            formSection.classList.remove('hide');
            formSection.classList.add('show');
            
            // Resetear formulario
            resetForm();
            
            // Scroll suave al formulario
            setTimeout(() => {
                formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
        
        // Función para ocultar el formulario
        function hideForm() {
            const formSection = document.getElementById('formSection');
            formSection.classList.remove('show');
            formSection.classList.add('hide');
            
            // Resetear formulario después de la animación
            setTimeout(() => {
                resetForm();
            }, 300);
        }
        
        function editUser(id) {
            const usuario = usuarios.find(u => u.id == id);
            if (!usuario) return;
            
            // Mostrar formulario si está oculto
            const formSection = document.getElementById('formSection');
            if (!formSection.classList.contains('show')) {
                formSection.classList.add('show');
                formSection.classList.remove('hide');
            }
            
            // Cambiar título del formulario
            document.getElementById('form-title').textContent = 'Editar Usuario';
            document.getElementById('form-action').value = 'editar';
            document.getElementById('user-id').value = id;
            document.getElementById('submit-text').textContent = '💾 Actualizar Usuario';
            
            // Llenar formulario
            document.getElementById('username').value = usuario.username;
            document.getElementById('nombre_completo').value = usuario.nombre_completo;
            document.getElementById('email').value = usuario.email;
            document.getElementById('rol').value = usuario.rol;
            document.getElementById('activo').value = usuario.activo;
            
            // Mostrar/ocultar campos
            document.getElementById('password-group').style.display = 'none';
            document.getElementById('activo-group').style.display = 'block';
            
            // Scroll al formulario
            setTimeout(() => {
                formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
        
        function resetForm() {
            document.getElementById('form-title').textContent = 'Crear Nuevo Usuario';
            document.getElementById('form-action').value = 'crear';
            document.getElementById('user-id').value = '';
            document.getElementById('submit-text').textContent = '💾 Crear Usuario';
            document.getElementById('userForm').reset();
            
            document.getElementById('password-group').style.display = 'block';
            document.getElementById('activo-group').style.display = 'none';
        }
        
        function deleteUser(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="eliminar">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function changePassword(id) {
            document.getElementById('password-user-id').value = id;
            document.getElementById('passwordModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('passwordModal').style.display = 'none';
            document.getElementById('passwordForm').reset();
        }
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('passwordModal');
            if (event.target === modal) {
                closeModal();
            }
        }
        
        // Validación en tiempo real
        document.getElementById('username').addEventListener('input', function() {
            const username = this.value;
            if (username.length < 3) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });
        
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            if (password.length < 4) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });
        
        // Manejar envío del formulario
        document.getElementById('userForm').addEventListener('submit', function(e) {
            // Validar campos requeridos
            const username = document.getElementById('username').value.trim();
            const nombreCompleto = document.getElementById('nombre_completo').value.trim();
            const password = document.getElementById('password').value;
            const action = document.getElementById('form-action').value;
            
            if (!username || !nombreCompleto) {
                e.preventDefault();
                alert('Por favor complete todos los campos obligatorios');
                return;
            }
            
            if (action === 'crear' && !password) {
                e.preventDefault();
                alert('La contraseña es obligatoria para crear un nuevo usuario');
                return;
            }
            
            if (action === 'crear' && password.length < 4) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 4 caracteres');
                return;
            }
            
            if (username.length < 3) {
                e.preventDefault();
                alert('El nombre de usuario debe tener al menos 3 caracteres');
                return;
            }
        });
        
        // Ocultar formulario al cargar la página si hay mensaje de éxito
        document.addEventListener('DOMContentLoaded', function() {
            const message = document.querySelector('.message.success');
            if (message) {
                // Si hay un mensaje de éxito, ocultar el formulario
                setTimeout(() => {
                    hideForm();
                }, 100);
            }
        });
    </script>
</body>
</html>