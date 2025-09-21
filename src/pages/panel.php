<?php
/**
 * Sistema de Registro de Niños - Panel Principal
 * 
 * Esta página muestra el panel principal del sistema con la lista
 * de todos los niños registrados y opciones de gestión.
 * 
 * @author Sistema Susana
 * @version 1.0.0
 * @since 2024
 */

// Incluir archivos necesarios
require_once '../../config/routes.php';
require_once '../../verificar_sesion.php';
require_once '../../conexion.php';
require_once '../../includes/functions.php';

// Manejo de mensajes de éxito y error
$message = '';
$message_type = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'eliminado':
            $message = '✅ Registro eliminado correctamente';
            $message_type = 'success';
            break;
        case 'registro_actualizado':
            $message = '✅ Registro actualizado correctamente';
            $message_type = 'success';
            break;
    }
}

if (isset($_GET['logout'])) {
    switch ($_GET['logout']) {
        case 'success':
            $message = '👋 Sesión cerrada correctamente';
            $message_type = 'success';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'id_invalido':
            $message = '❌ ID de registro inválido';
            $message_type = 'error';
            break;
        case 'registro_no_encontrado':
            $message = '❌ Registro no encontrado';
            $message_type = 'error';
            break;
        case 'error_eliminacion':
            $message = '❌ Error al eliminar el registro';
            $message_type = 'error';
            break;
        case 'error_guardado':
            $message = '❌ Error al guardar el registro';
            $message_type = 'error';
            break;
        case 'id_faltante':
            $message = '❌ ID faltante en la consulta';
            $message_type = 'error';
            break;
    }
}

// Buscar
$busqueda = "";
if (isset($_GET['buscar'])) {
    $busqueda = $conn->real_escape_string($_GET['buscar']);
    $sql = "SELECT * FROM ninos 
            WHERE ci_nino LIKE '%$busqueda%' 
            OR nombre_completo LIKE '%$busqueda%' 
            ORDER BY fecha_registro DESC";
} else {
    $sql = "SELECT * FROM ninos ORDER BY fecha_registro DESC";
}

$result = $conn->query($sql);

// Verificar si hubo error en la consulta
if (!$result) {
    $message = '❌ Error en la consulta: ' . $conn->error;
    $message_type = 'error';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>📋 Panel Principal - Sistema Susana</title>
  <link rel="stylesheet" href="<?= css_url('menu.css') ?>">
  <link rel="stylesheet" href="<?= css_url('estilos-corporativos.css') ?>">
  <script src="<?= js_url('sistema.js') ?>"></script>
  <style>
    body { 
      font-family: 'Montserrat', sans-serif; 
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); 
      margin: 0; 
      padding: 0; 
    }
    .main-content { 
      padding: 130px 20px; 
      max-width: 1200px;
      margin: 0 auto;
    }
    h2 { 
      text-align: center; 
      margin-top: 0; 
      margin-bottom: 30px; 
      color: #1c2c50; 
      font-size: 2.5em; 
      font-weight: 700; 
      text-transform: uppercase;
      letter-spacing: 2px;
    }
    table { 
      width: 100%; 
      border-collapse: collapse; 
      background: #FFFFFF; 
      margin-top: 20px; 
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(28,44,80,0.15);
    }
    th, td { 
      border: 1px solid #e9ecef; 
      padding: 12px; 
      text-align: left; 
    }
    th { 
      background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%); 
      color: #FFFFFF; 
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    tr:nth-child(even) { 
      background: #f8f9fa; 
    }
    tr:hover {
      background: rgba(221,166,25,0.1);
    }
    .acciones { 
      text-align: center;
      white-space: nowrap;
    }
    
    .acciones a { 
      display: inline-block;
      margin: 0 3px; 
      text-decoration: none; 
      padding: 8px 12px;
      border-radius: 6px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      font-size: 12px;
      min-width: 60px;
    }
    .acciones a:nth-child(1) {
      background: #28a745;
      color: white;
    }
    .acciones a:nth-child(2) {
      background: #dda619;
      color: white;
    }
    .acciones a:nth-child(3) {
      background: #a5221c;
      color: white;
    }
    .acciones a:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .buscar { 
      text-align: center; 
      margin-bottom: 30px; 
      background: #FFFFFF;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(28,44,80,0.1);
    }
    input[type="text"] { 
      padding: 12px 16px; 
      width: 300px; 
      border: 2px solid #e9ecef;
      border-radius: 8px;
      font-family: 'Montserrat', sans-serif;
      transition: all 0.3s ease;
    }
    input[type="text"]:focus {
      border-color: #dda619;
      box-shadow: 0 0 0 3px rgba(221,166,25,0.1);
      outline: none;
    }
    button { 
      padding: 12px 24px; 
      background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%); 
      color: #FFFFFF; 
      border: none; 
      border-radius: 8px;
      cursor: pointer; 
      font-family: 'Montserrat', sans-serif;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      margin-left: 10px;
    }
    button:hover { 
      background: linear-gradient(135deg, #2a4a7a 0%, #1c2c50 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(28,44,80,0.3);
    }
    .message {
      padding: 15px 20px;
      margin: 20px 0;
      border-radius: 8px;
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
    
    @media (max-width: 768px) {
      .main-content {
        padding: 130px 10px 20px;
      }
      
      table {
        font-size: 12px;
      }
      
      th, td {
        padding: 8px 4px;
      }
      
      .acciones a {
        padding: 6px 8px;
        font-size: 10px;
        margin: 0 2px;
        min-width: 50px;
      }
    }
    
    @media (max-width: 480px) {
      .acciones a {
        padding: 5px 6px;
        font-size: 9px;
        margin: 0 1px;
        min-width: 45px;
      }
    }
  </style>
</head>
<body>
  <!-- Menú de navegación profesional -->
  <nav class="navbar">
    <div class="nav-container">
      <a href="<?= PANEL_URL ?>" class="nav-logo">
        <img src="<?= image_url('logo.png') ?>" alt="Logo del Sistema" class="logo-icon"> SISTEMA
      </a>
      <ul class="nav-menu">
        <li class="nav-item">
          <a href="<?= REGISTRO_URL ?>" class="nav-link">
            <span class="icon">📝</span> Nuevo Registro
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= PANEL_URL ?>" class="nav-link active">
            <span class="icon">📋</span> Panel Principal
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= ESTADISTICAS_URL ?>" class="nav-link">
            <span class="icon">📊</span> Estadísticas
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= REPORTES_URL ?>" class="nav-link">
            <span class="icon">📄</span> Reportes
          </a>
        </li>
        <li class="nav-item">
          <a href="admin/usuarios" class="nav-link">
            <span class="icon">👥</span> Gestión Usuarios
          </a>
        </li>
        <li class="nav-item">
          <a href="auth/logout" class="nav-link">
            <span class="icon">🚪</span> Cerrar Sesión
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="main-content">
    <h2>📋 Panel de Registro de Niños</h2>

  <?php if ($message): ?>
    <div class="message <?= $message_type ?>"><?= $message ?></div>
  <?php endif; ?>

  <div class="buscar">
    <form method="GET" action="panel.php">
      <input type="text" name="buscar" placeholder="Buscar por C.I. o nombre" value="<?= htmlspecialchars($busqueda) ?>">
      <button type="submit">Buscar</button>
      <a href="panel.php"><button type="button">Mostrar todos</button></a>
    </form>
  </div>

  <table>
    <tr>
      <th>ID</th>
      <th>C.I. Niño</th>
      <th>Nombre</th>
      <th>Fecha Nac.</th>
      <th>Sexo</th>
      <th>Provincia</th>
      <th>Activo</th>
      <th>Acciones</th>
    </tr>
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['ci_nino']) ?></td>
        <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
        <td><?= htmlspecialchars($row['fecha_nacimiento']) ?></td>
        <td><?= htmlspecialchars($row['sexo']) ?></td>
        <td><?= htmlspecialchars($row['provincia']) ?></td>
        <td><?= htmlspecialchars($row['estudiante_activo']) ?></td>
        <td class="acciones">
          <a href="ver?id=<?= $row['id'] ?>">👁 Ver</a>
          <a href="editar?id=<?= $row['id'] ?>">✏ Editar</a>
          <a href="eliminar?id=<?= $row['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este registro?')">🗑 Eliminar</a>
        </td>
      </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="8" style="text-align: center; padding: 20px;">
          <?= $message_type === 'error' ? 'Error al cargar los datos' : 'No se encontraron registros' ?>
        </td>
      </tr>
    <?php endif; ?>
  </table>
  </div> <!-- Cierre del main-content -->
</body>
</html>
