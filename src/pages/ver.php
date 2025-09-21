<?php
include '../../verificar_sesion.php';
include '../../conexion.php';

// Validar que se proporcione un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: panel.php?error=id_faltante");
    exit();
}

$id = intval($_GET['id']);

// Verificar que el ID sea válido
if ($id <= 0) {
    header("Location: panel.php?error=id_invalido");
    exit();
}

$sql_nino = "SELECT * FROM ninos WHERE id = $id";
$result_nino = $conn->query($sql_nino);

// Verificar que el registro existe
if ($result_nino->num_rows === 0) {
    header("Location: panel.php?error=registro_no_encontrado");
    exit();
}

$nino = $result_nino->fetch_assoc();

$sql_rep = "SELECT * FROM representantes WHERE id_nino = $id";
$representantes = $conn->query($sql_rep);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Detalles del Niño</title>
  <link rel="stylesheet" href="../../public/assets/css/menu.css">
  <link rel="stylesheet" href="../../public/assets/css/estilos-corporativos.css">
  <style>
    body { 
      font-family: 'Montserrat', sans-serif; 
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      margin: 0; 
      padding: 0; 
      min-height: 100vh;
    }
    
    .main-content { 
      max-width: 900px; 
      margin: 0 auto; 
      padding: 100px 20px 40px; 
    }
    
    .card-container {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
      overflow: hidden;
      position: relative;
    }
    
    .card-header {
      background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%);
      color: white;
      padding: 40px;
      text-align: center;
      position: relative;
    }
    
    .card-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      opacity: 0.3;
    }
    
    .child-avatar {
      width: 120px;
      height: 120px;
      background: rgba(255,255,255,0.2);
      border-radius: 50%;
      margin: 0 auto 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 4em;
      border: 4px solid rgba(255,255,255,0.3);
      position: relative;
      z-index: 1;
    }
    
    .child-name {
      font-size: 2.5em;
      font-weight: 700;
      margin: 0 0 10px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
      position: relative;
      z-index: 1;
      color: white;

    }
    
    .child-ci {
      font-size: 1.3em;
      opacity: 0.9;
      font-weight: 300;
      position: relative;
      z-index: 1;
    }
    
    .card-body {
      padding: 40px;
    }
    
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      margin-bottom: 40px;
    }
    
    .info-section {
      background: #f8f9fa;
      border-radius: 15px;
      padding: 25px;
      border-left: 5px solid #dda619;
      box-shadow: 0 4px 12px rgba(28,44,80,0.1);
    }
    
    .info-section h3 {
      color: #1c2c50;
      margin: 0 0 20px;
      font-size: 1.4em;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .info-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid #e9ecef;
    }
    
    .info-item:last-child {
      border-bottom: none;
    }
    
    .info-label {
      font-weight: 600;
      color: #1c2c50;
      font-size: 0.95em;
      font-family: 'Montserrat', sans-serif;
    }
    
    .info-value {
      color: #1c2c50;
      font-weight: 500;
      text-align: right;
      max-width: 60%;
      font-family: 'Montserrat', sans-serif;
    }
    
    .representatives-section {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border-radius: 15px;
      padding: 30px;
      margin-top: 30px;
      border-left: 5px solid #dda619;
      box-shadow: 0 4px 12px rgba(28,44,80,0.1);
    }
    
    .representatives-section h3 {
      color: #1c2c50;
      margin: 0 0 25px;
      font-size: 1.5em;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .representative-card {
      background: white;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 15px;
      box-shadow: 0 4px 8px rgba(28,44,80,0.1);
      border-left: 4px solid #dda619;
    }
    
    .representative-card:last-child {
      margin-bottom: 0;
    }
    
    .rep-name {
      font-size: 1.2em;
      font-weight: 600;
      color: #1c2c50;
      margin-bottom: 8px;
      font-family: 'Montserrat', sans-serif;
    }
    
    .rep-details {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 10px;
      font-size: 0.9em;
      color: #6c757d;
    }
    
    .rep-detail {
      display: flex;
      justify-content: space-between;
    }
    
    .rep-label {
      font-weight: 500;
      color: #1c2c50;
      font-family: 'Montserrat', sans-serif;
    }
    
    .action-buttons {
      text-align: center;
      margin-top: 40px;
      padding-top: 30px;
      border-top: 2px solid #e9ecef;
    }
    
    .btn {
      display: inline-block;
      padding: 12px 30px;
      margin: 0 10px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%);
      color: white;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(28,44,80,0.3);
    }
    
    .btn-secondary {
      background: linear-gradient(135deg, #dda619 0%, #c49a0f 100%);
      color: white;
    }
    
    .btn-secondary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(221,166,25,0.3);
    }
    
    .status-badge {
      display: inline-block;
      padding: 6px 15px;
      border-radius: 20px;
      font-size: 0.85em;
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
    
    .status-disabled {
      background: linear-gradient(135deg, #dda619 0%, #c49a0f 100%);
      color: white;
    }
    
    .status-enabled {
      background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%);
      color: white;
    }
    
    @media (max-width: 768px) {
      .main-content {
        padding: 100px 10px 20px;
      }
      
      .card-header {
        padding: 30px 20px;
      }
      
      .child-name {
        font-size: 2em;
      }
      
      .card-body {
        padding: 30px 20px;
      }
      
      .info-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .btn {
        display: block;
        margin: 10px 0;
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <!-- Menú de navegación profesional -->
  <nav class="navbar">
    <div class="nav-container">
      <a href="../../panel" class="nav-logo">
        <img src="../../public/assets/images/logo.png" alt="Logo del Sistema" class="logo-icon"> SISTEMA
      </a>
      <ul class="nav-menu">
        <li class="nav-item">
          <a href="../../public/registro.html" class="nav-link">
            <span class="icon">📝</span> Nuevo Registro
          </a>
        </li>
        <li class="nav-item">
          <a href="../../panel" class="nav-link">
            <span class="icon">📋</span> Panel Principal
          </a>
        </li>
        <li class="nav-item">
          <a href="../../src/auth/auth.php" class="nav-link">
            <span class="icon">🚪</span> Cerrar Sesión
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="main-content">
    <div class="card-container">
      <!-- Header de la tarjeta -->
      <div class="card-header">
        <div class="child-avatar">
          <?= $nino['sexo'] === 'Masculino' ? '👦' : '👧' ?>
        </div>
        <h1 class="child-name"><?= htmlspecialchars($nino['nombre_completo']) ?></h1>
        <div class="child-ci">C.I. <?= htmlspecialchars($nino['ci_nino']) ?></div>
      </div>
      
      <!-- Cuerpo de la tarjeta -->
      <div class="card-body">
        <!-- Información personal -->
        <div class="info-grid">
          <div class="info-section">
            <h3>📋 Información Personal</h3>
            <div class="info-item">
              <span class="info-label">Fecha de Nacimiento</span>
              <span class="info-value"><?= htmlspecialchars($nino['fecha_nacimiento']) ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Sexo</span>
              <span class="info-value"><?= htmlspecialchars($nino['sexo']) ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Estado Académico</span>
              <span class="info-value">
                <span class="status-badge <?= $nino['estudiante_activo'] === 'Si' ? 'status-active' : 'status-inactive' ?>">
                  <?= htmlspecialchars($nino['estudiante_activo']) ?>
                </span>
              </span>
            </div>
            <?php if (!empty($nino['grado'])): ?>
            <div class="info-item">
              <span class="info-label">Grado</span>
              <span class="info-value"><?= htmlspecialchars($nino['grado']) ?></span>
            </div>
            <?php endif; ?>
          </div>
          
          <div class="info-section">
            <h3>📍 Ubicación</h3>
            <div class="info-item">
              <span class="info-label">Provincia</span>
              <span class="info-value"><?= htmlspecialchars($nino['provincia']) ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Cantón</span>
              <span class="info-value"><?= htmlspecialchars($nino['canton']) ?></span>
            </div>
            <?php if (!empty($nino['parroquia'])): ?>
            <div class="info-item">
              <span class="info-label">Parroquia</span>
              <span class="info-value"><?= htmlspecialchars($nino['parroquia']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($nino['barrio'])): ?>
            <div class="info-item">
              <span class="info-label">Barrio</span>
              <span class="info-value"><?= htmlspecialchars($nino['barrio']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($nino['direccion'])): ?>
            <div class="info-item">
              <span class="info-label">Dirección</span>
              <span class="info-value"><?= htmlspecialchars($nino['direccion']) ?></span>
            </div>
            <?php endif; ?>
          </div>
        </div>
        
        <!-- Información de discapacidad -->
        <div class="info-section">
          <h3>♿ Información de Discapacidad</h3>
          <div class="info-item">
            <span class="info-label">Estado</span>
            <span class="info-value">
              <span class="status-badge <?= $nino['discapacitado'] === 'Si' ? 'status-disabled' : 'status-enabled' ?>">
                <?= htmlspecialchars($nino['discapacitado']) ?>
              </span>
            </span>
          </div>
          <?php if (!empty($nino['detalle_discapacidad'])): ?>
          <div class="info-item">
            <span class="info-label">Detalle</span>
            <span class="info-value"><?= htmlspecialchars($nino['detalle_discapacidad']) ?></span>
          </div>
          <?php endif; ?>
        </div>
        
        <!-- Representantes -->
        <div class="representatives-section">
          <h3>👨‍👩‍👧‍👦 Representantes Legales</h3>
    <?php while ($rep = $representantes->fetch_assoc()): ?>
          <div class="representative-card">
            <div class="rep-name"><?= htmlspecialchars($rep['nombre']) ?></div>
            <div class="rep-details">
              <div class="rep-detail">
                <span class="rep-label">Cédula:</span>
                <span><?= htmlspecialchars($rep['ci']) ?></span>
              </div>
              <div class="rep-detail">
                <span class="rep-label">Parentesco:</span>
                <span><?= htmlspecialchars($rep['parentesco']) ?></span>
              </div>
              <div class="rep-detail">
                <span class="rep-label">Teléfono:</span>
                <span><?= htmlspecialchars($rep['telefono']) ?></span>
              </div>
              <div class="rep-detail">
                <span class="rep-label">Tipo:</span>
                <span>Representante <?= htmlspecialchars($rep['representante_numero']) ?></span>
              </div>
            </div>
          </div>
    <?php endwhile; ?>
        </div>
        
        <!-- Botones de acción -->
        <div class="action-buttons">
          <a href="../../editar?id=<?= $nino['id'] ?>" class="btn btn-primary">
            ✏️ Editar Registro
          </a>
          <a href="../../panel" class="btn btn-secondary">
            ⬅️ Volver al Panel
          </a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
