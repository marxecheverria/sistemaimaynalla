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

// Obtener datos del niño
$sql_nino = "SELECT * FROM ninos WHERE id = $id";
$result_nino = $conn->query($sql_nino);

// Verificar que el registro existe
if ($result_nino->num_rows === 0) {
    header("Location: panel.php?error=registro_no_encontrado");
    exit();
}

$nino = $result_nino->fetch_assoc();

// Obtener representantes
$sql_rep = "SELECT * FROM representantes WHERE id_nino = $id ORDER BY representante_numero ASC";
$result_reps = $conn->query($sql_rep);

$rep1 = $rep2 = null;
while ($rep = $result_reps->fetch_assoc()) {
    if ($rep['representante_numero'] == '1') {
        $rep1 = $rep;
    } elseif ($rep['representante_numero'] == '2') {
        $rep2 = $rep;
    }
}

// Guardar cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ci_nino = $conn->real_escape_string(trim($_POST['ciNiño']));
    $nombre_nino = $conn->real_escape_string(trim($_POST['nombreNiño']));
    $fecha_nacimiento = $conn->real_escape_string(trim($_POST['fechaNacimiento']));
    $sexo = $conn->real_escape_string(trim($_POST['sexo']));
    $provincia = $conn->real_escape_string(trim($_POST['provincia']));
    $canton = $conn->real_escape_string(trim($_POST['canton']));
    $parroquia = $conn->real_escape_string(trim($_POST['parroquia'] ?? ''));
    $barrio = $conn->real_escape_string(trim($_POST['barrio'] ?? ''));
    $direccion = $conn->real_escape_string(trim($_POST['direccion'] ?? ''));
    $activo = $conn->real_escape_string(trim($_POST['activo']));
    $grado = $conn->real_escape_string(trim($_POST['grado'] ?? ''));
    $discapacidad = $conn->real_escape_string(trim($_POST['discapacidad']));
    $detalle_discapacidad = $conn->real_escape_string(trim($_POST['detalleDiscapacidad'] ?? ''));

    // Actualizar datos del niño
    $sql_update_nino = "UPDATE ninos SET 
        ci_nino='$ci_nino',
        nombre_completo='$nombre_nino',
        fecha_nacimiento='$fecha_nacimiento',
        sexo='$sexo',
        provincia='$provincia',
        canton='$canton',
        parroquia='$parroquia',
        barrio='$barrio',
        direccion='$direccion',
        estudiante_activo='$activo',
        grado='$grado',
        discapacitado='$discapacidad',
        detalle_discapacidad='$detalle_discapacidad'
        WHERE id = $id";

    if ($conn->query($sql_update_nino)) {
        // Actualizar representante 1
        $rep1_nombre = $conn->real_escape_string(trim($_POST['representante1']));
        $rep1_ci = $conn->real_escape_string(trim($_POST['ciRepresentante1']));
        $rep1_parentesco = $conn->real_escape_string(trim($_POST['parentesco1']));
        $rep1_telefono = $conn->real_escape_string(trim($_POST['telefono1']));

        $sql_update_rep1 = "UPDATE representantes SET 
            nombre='$rep1_nombre',
            ci='$rep1_ci',
            parentesco='$rep1_parentesco',
            telefono='$rep1_telefono'
            WHERE id_nino = $id AND representante_numero = '1'";

        $conn->query($sql_update_rep1);

        // Actualizar representante 2 si existe
        if (!empty($_POST['representante2']) || !empty($_POST['telefono2'])) {
            $rep2_nombre = $conn->real_escape_string(trim($_POST['representante2']));
            $rep2_telefono = $conn->real_escape_string(trim($_POST['telefono2']));

            if ($rep2) {
                // Actualizar representante existente
                $sql_update_rep2 = "UPDATE representantes SET 
                    nombre='$rep2_nombre',
                    telefono='$rep2_telefono'
                    WHERE id_nino = $id AND representante_numero = '2'";
            } else {
                // Crear nuevo representante
                $sql_update_rep2 = "INSERT INTO representantes (id_nino, nombre, telefono, representante_numero) 
                    VALUES ($id, '$rep2_nombre', '$rep2_telefono', '2')";
            }
            $conn->query($sql_update_rep2);
        }

        header("Location: panel.php?success=registro_actualizado");
        exit();
    } else {
        $error_message = "Error al actualizar: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Registro</title>
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
      max-width: 1000px;
      margin: 0 auto;
      padding: 100px 20px 40px;
    }
    
    .form-container {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
      overflow: hidden;
      position: relative;
    }
    
    .form-header {
      background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%);
      color: white;
      padding: 40px;
      text-align: center;
      position: relative;
    }
    
    .form-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      opacity: 0.3;
    }
    
    .form-title {
      font-size: 2.5em;
      font-weight: 700;
      margin: 0 0 10px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
      position: relative;
      z-index: 1;
      color: white;
    }
    
    .form-subtitle {
      font-size: 1.2em;
      opacity: 0.9;
      font-weight: 300;
      position: relative;
      z-index: 1;
    }
    
    .form-body {
      padding: 40px;
    }
    
    .form-section {
      margin-bottom: 40px;
      background: #f8f9fa;
      border-radius: 15px;
      padding: 30px;
      border-left: 5px solid #dda619;
      position: relative;
      box-shadow: 0 4px 12px rgba(28,44,80,0.1);
    }
    
    .section-header {
      display: flex;
      align-items: center;
      margin-bottom: 25px;
      color: #1c2c50;
    }
    
    .section-icon {
      font-size: 2em;
      margin-right: 15px;
    }
    
    .section-title {
      font-size: 1.5em;
      font-weight: 600;
      margin: 0;
    }
    
    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 25px;
    }
    
    .form-group {
      position: relative;
    }
    
    .form-group.full-width {
      grid-column: 1 / -1;
    }
    
    .form-label {
      display: flex;
      align-items: center;
      margin-bottom: 8px;
      font-weight: 600;
      color: #1c2c50;
      font-size: 1em;
      font-family: 'Montserrat', sans-serif;
    }
    
    .label-icon {
      margin-right: 8px;
      font-size: 1.1em;
    }
    
    .required-indicator {
      color: #dc3545;
      margin-left: 5px;
      font-weight: bold;
    }
    
    .form-input {
      width: 100%;
      padding: 15px 20px;
      border: 2px solid #e9ecef;
      border-radius: 12px;
      font-size: 1em;
      transition: all 0.3s ease;
      background: #fff;
      box-sizing: border-box;
    }
    
    .form-input:focus {
      outline: none;
      border-color: #dda619;
      box-shadow: 0 0 0 3px rgba(221,166,25,0.1);
      transform: translateY(-2px);
    }
    
    .form-input.error {
      border-color: #dc3545;
      box-shadow: 0 0 0 3px rgba(220,53,69,0.1);
    }
    
    .form-input.success {
      border-color: #28a745;
      box-shadow: 0 0 0 3px rgba(40,167,69,0.1);
    }
    
    .form-select {
      width: 100%;
      padding: 15px 20px;
      border: 2px solid #e9ecef;
      border-radius: 12px;
      font-size: 1em;
      background: #fff;
      cursor: pointer;
      transition: all 0.3s ease;
      box-sizing: border-box;
    }
    
    .form-select:focus {
      outline: none;
      border-color: #dda619;
      box-shadow: 0 0 0 3px rgba(221,166,25,0.1);
    }
    
    .form-select:disabled {
      background: #f8f9fa;
      color: #6c757d;
      cursor: not-allowed;
    }
    
    .form-textarea {
      width: 100%;
      padding: 15px 20px;
      border: 2px solid #e9ecef;
      border-radius: 12px;
      font-size: 1em;
      resize: vertical;
      min-height: 100px;
      transition: all 0.3s ease;
      box-sizing: border-box;
    }
    
    .form-textarea:focus {
      outline: none;
      border-color: #007BFF;
      box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }
    
    .radio-group {
      display: flex;
      gap: 30px;
      margin-top: 10px;
    }
    
    .radio-item {
      display: flex;
      align-items: center;
      padding: 15px 20px;
      background: #fff;
      border: 2px solid #e9ecef;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      flex: 1;
    }
    
    .radio-item:hover {
      border-color: #dda619;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(221,166,25,0.1);
    }
    
    .radio-item.selected {
      border-color: #dda619;
      background: rgba(221,166,25,0.05);
    }
    
    .radio-item input[type="radio"] {
      margin-right: 10px;
      transform: scale(1.2);
    }
    
    .radio-item label {
      cursor: pointer;
      font-weight: 500;
      color: #495057;
      margin: 0;
    }
    
    
    .error-message {
      color: #dc3545;
      font-size: 0.9em;
      margin-top: 5px;
      display: none;
      align-items: center;
    }
    
    .error-icon {
      margin-right: 5px;
    }
    
    .form-actions {
      text-align: center;
      margin-top: 40px;
      padding-top: 30px;
      border-top: 2px solid #e9ecef;
    }
    
    .btn {
      display: inline-block;
      padding: 15px 40px;
      margin: 0 10px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      font-size: 1.1em;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%);
      color: white;
    }
    
    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(28,44,80,0.3);
    }
    
    .btn-secondary {
      background: linear-gradient(135deg, #dda619 0%, #c49a0f 100%);
      color: white;
    }
    
    .btn-secondary:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(221,166,25,0.3);
    }
    
    .floating-help {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%);
      color: white;
      padding: 15px 20px;
      border-radius: 25px;
      box-shadow: 0 8px 20px rgba(28,44,80,0.3);
      cursor: pointer;
      transition: all 0.3s ease;
      z-index: 1000;
      font-family: 'Montserrat', sans-serif;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .floating-help:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 25px rgba(28,44,80,0.4);
    }
    
    @media (max-width: 768px) {
      .main-content {
        padding: 100px 10px 20px;
      }
      
      .form-header {
        padding: 30px 20px;
      }
      
      .form-title {
        font-size: 2em;
      }
      
      .form-body {
        padding: 30px 20px;
      }
      
      .form-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .radio-group {
        flex-direction: column;
        gap: 15px;
      }
      
      .btn {
        display: block;
        margin: 10px 0;
        width: 100%;
      }
      
      .floating-help {
        bottom: 20px;
        right: 20px;
        padding: 12px 16px;
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
    <div class="form-container">
      <!-- Header del formulario -->
      <div class="form-header">
        <h1 class="form-title">✏️ Editar Registro</h1>
        
      </div>
      
      <!-- Cuerpo del formulario -->
      <div class="form-body">
        <form id="editarForm" method="POST">
          
          <!-- Sección 1: Datos Personales -->
          <div class="form-section">
            <div class="section-header">
              <span class="section-icon">👦</span>
              <h3 class="section-title">Datos Personales del Niño</h3>
            </div>
            
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">🆔</span>
                  Cédula de Identidad
                  <span class="required-indicator">*</span>
                </label>
                <input type="text" id="ciNiño" name="ciNiño" class="form-input" required pattern="^[0-9]{10}$" value="<?= htmlspecialchars($nino['ci_nino']) ?>">
                <div class="error-message" id="ciNiñoError">
                  <span class="error-icon">❌</span>
                  La cédula debe tener exactamente 10 dígitos
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">👤</span>
                  Apellidos y Nombres Completos
                  <span class="required-indicator">*</span>
                </label>
                <input type="text" id="nombreNiño" name="nombreNiño" class="form-input" required value="<?= htmlspecialchars($nino['nombre_completo']) ?>">
                <div class="error-message" id="nombreNiñoError">
                  <span class="error-icon">❌</span>
                  Este campo es obligatorio
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">📅</span>
                  Fecha de Nacimiento
                  <span class="required-indicator">*</span>
                </label>
                <input type="date" id="fechaNacimiento" name="fechaNacimiento" class="form-input" required value="<?= htmlspecialchars($nino['fecha_nacimiento']) ?>">
                <div class="error-message" id="fechaNacimientoError">
                  <span class="error-icon">❌</span>
                  Seleccione una fecha válida
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">⚥</span>
                  Sexo
                  <span class="required-indicator">*</span>
                </label>
                <div class="radio-group">
                  <div class="radio-item <?= $nino['sexo'] === 'Masculino' ? 'selected' : '' ?>" onclick="selectRadio('sexo', 'Masculino')">
                    <input type="radio" name="sexo" value="Masculino" required <?= $nino['sexo'] === 'Masculino' ? 'checked' : '' ?>>
                    <label>👦 Masculino</label>
                  </div>
                  <div class="radio-item <?= $nino['sexo'] === 'Femenino' ? 'selected' : '' ?>" onclick="selectRadio('sexo', 'Femenino')">
                    <input type="radio" name="sexo" value="Femenino" required <?= $nino['sexo'] === 'Femenino' ? 'checked' : '' ?>>
                    <label>👧 Femenino</label>
                  </div>
                </div>
                <div class="error-message" id="sexoError">
                  <span class="error-icon">❌</span>
                  Seleccione una opción
                </div>
              </div>
            </div>
          </div>

          <!-- Sección 2: Ubicación -->
          <div class="form-section">
            <div class="section-header">
              <span class="section-icon">📍</span>
              <h3 class="section-title">Ubicación y Dirección</h3>
            </div>
            
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">🏛️</span>
                  Provincia
                  <span class="required-indicator">*</span>
                </label>
                <select id="provincia" name="provincia" class="form-select" required>
                  <option value="">Seleccione una provincia</option>
                </select>
                <div class="error-message" id="provinciaError">
                  <span class="error-icon">❌</span>
                  Este campo es obligatorio
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">🏘️</span>
                  Cantón
                  <span class="required-indicator">*</span>
                </label>
                <select id="canton" name="canton" class="form-select" required disabled>
                  <option value="">Seleccione un cantón</option>
                </select>
                <div class="error-message" id="cantonError">
                  <span class="error-icon">❌</span>
                  Este campo es obligatorio
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">🏠</span>
                  Parroquia
                </label>
                <select id="parroquia" name="parroquia" class="form-select" disabled>
                  <option value="">Seleccione una parroquia</option>
                </select>
              </div>
              
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">🏘️</span>
                  Barrio
                </label>
                <input type="text" id="barrio" name="barrio" class="form-input" value="<?= htmlspecialchars($nino['barrio']) ?>">
              </div>
              
              <div class="form-group full-width">
                <label class="form-label">
                  <span class="label-icon">🏠</span>
                  Dirección Completa
                </label>
                <textarea id="direccion" name="direccion" class="form-textarea"><?= htmlspecialchars($nino['direccion']) ?></textarea>
              </div>
            </div>
          </div>

          <!-- Sección 3: Información Académica -->
          <div class="form-section">
            <div class="section-header">
              <span class="section-icon">🎓</span>
              <h3 class="section-title">Información Académica</h3>
            </div>
            
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">📚</span>
                  Estado Académico
                  <span class="required-indicator">*</span>
                </label>
                <div class="radio-group">
                  <div class="radio-item <?= $nino['estudiante_activo'] === 'Si' ? 'selected' : '' ?>" onclick="selectRadio('activo', 'Si')">
                    <input type="radio" name="activo" value="Si" required <?= $nino['estudiante_activo'] === 'Si' ? 'checked' : '' ?>>
                    <label>✅ Sí, está estudiando</label>
                  </div>
                  <div class="radio-item <?= $nino['estudiante_activo'] === 'No' ? 'selected' : '' ?>" onclick="selectRadio('activo', 'No')">
                    <input type="radio" name="activo" value="No" required <?= $nino['estudiante_activo'] === 'No' ? 'checked' : '' ?>>
                    <label>❌ No está estudiando</label>
                  </div>
                </div>
                <div class="error-message" id="activoError">
                  <span class="error-icon">❌</span>
                  Seleccione una opción
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">📖</span>
                  Grado o Nivel
                </label>
                <input type="text" id="grado" name="grado" class="form-input" value="<?= htmlspecialchars($nino['grado']) ?>">
              </div>
            </div>
          </div>
          
          <!-- Sección 4: Información de Discapacidad -->
          <div class="form-section">
            <div class="section-header">
              <span class="section-icon">♿</span>
              <h3 class="section-title">Información de Discapacidad</h3>
            </div>
            
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">🔍</span>
                  ¿Tiene alguna discapacidad?
                  <span class="required-indicator">*</span>
                </label>
                <div class="radio-group">
                  <div class="radio-item <?= $nino['discapacitado'] === 'Si' ? 'selected' : '' ?>" onclick="selectRadio('discapacidad', 'Si')">
                    <input type="radio" name="discapacidad" value="Si" required <?= $nino['discapacitado'] === 'Si' ? 'checked' : '' ?>>
                    <label>♿ Sí, tiene discapacidad</label>
                  </div>
                  <div class="radio-item <?= $nino['discapacitado'] === 'No' ? 'selected' : '' ?>" onclick="selectRadio('discapacidad', 'No')">
                    <input type="radio" name="discapacidad" value="No" required <?= $nino['discapacitado'] === 'No' ? 'checked' : '' ?>>
                    <label>✅ No tiene discapacidad</label>
                  </div>
                </div>
                <div class="error-message" id="discapacidadError">
                  <span class="error-icon">❌</span>
                  Seleccione una opción
                </div>
              </div>
              
              <div class="form-group full-width">
                <label class="form-label">
                  <span class="label-icon">📝</span>
                  Detalle de Discapacidad
                </label>
                <textarea id="detalleDiscapacidad" name="detalleDiscapacidad" class="form-textarea"><?= htmlspecialchars($nino['detalle_discapacidad']) ?></textarea>
              </div>
            </div>
          </div>
          
          <!-- Sección 5: Representantes -->
          <div class="form-section">
            <div class="section-header">
              <span class="section-icon">👨‍👩‍👧‍👦</span>
              <h3 class="section-title">Representantes Legales</h3>
            </div>
            
            <!-- Representante 1 -->
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">👤</span>
                  Nombre del Representante Principal
                  <span class="required-indicator">*</span>
                </label>
                <input type="text" id="representante1" name="representante1" class="form-input" required value="<?= htmlspecialchars($rep1['nombre'] ?? '') ?>">
                <div class="error-message" id="representante1Error">
                  <span class="error-icon">❌</span>
                  Este campo es obligatorio
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">🆔</span>
                  Cédula del Representante
                  <span class="required-indicator">*</span>
                </label>
                <input type="text" id="ciRepresentante1" name="ciRepresentante1" class="form-input" required pattern="^[0-9]{10}$" value="<?= htmlspecialchars($rep1['ci'] ?? '') ?>">
                <div class="error-message" id="ciRepresentante1Error">
                  <span class="error-icon">❌</span>
                  La cédula debe tener exactamente 10 dígitos
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">👨‍👩‍👧‍👦</span>
                  Parentesco
                  <span class="required-indicator">*</span>
                </label>
                <input type="text" id="parentesco1" name="parentesco1" class="form-input" required value="<?= htmlspecialchars($rep1['parentesco'] ?? '') ?>">
                <div class="error-message" id="parentesco1Error">
                  <span class="error-icon">❌</span>
                  Este campo es obligatorio
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">
                  <span class="label-icon">📞</span>
                  Teléfono de Contacto
                  <span class="required-indicator">*</span>
                </label>
                <input type="tel" id="telefono1" name="telefono1" class="form-input" required pattern="^[0-9]{7,10}$" value="<?= htmlspecialchars($rep1['telefono'] ?? '') ?>">
                <div class="error-message" id="telefono1Error">
                  <span class="error-icon">❌</span>
                  Ingrese un teléfono válido (7-10 dígitos)
                </div>
              </div>
            </div>
            
            <!-- Representante 2 -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e9ecef;">
              <h4 style="color: #1e3c72; margin-bottom: 20px; display: flex; align-items: center;">
                <span style="margin-right: 10px;">👥</span>
                Representante Secundario (Opcional)
              </h4>
              
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">
                    <span class="label-icon">👤</span>
                    Nombre del Segundo Representante
                  </label>
                  <input type="text" id="representante2" name="representante2" class="form-input" value="<?= htmlspecialchars($rep2['nombre'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                  <label class="form-label">
                    <span class="label-icon">📞</span>
                    Teléfono del Segundo Representante
                  </label>
                  <input type="tel" id="telefono2" name="telefono2" class="form-input" pattern="^[0-9]{7,10}$" value="<?= htmlspecialchars($rep2['telefono'] ?? '') ?>">
                </div>
              </div>
            </div>
          </div>
          
          <!-- Botones de acción -->
          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <span style="margin-right: 8px;">💾</span>
              Guardar Cambios
            </button>
            <a href="../../panel" class="btn btn-secondary">
              <span style="margin-right: 8px;">⬅️</span>
              Cancelar
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <!-- Ayuda flotante -->
  <div class="floating-help" onclick="showHelp()">
    <span style="margin-right: 8px;">❓</span>
    ¿Necesitas ayuda?
  </div>

   <script>
     // Datos actuales del niño
     const provinciaActual = '<?= $nino['provincia'] ?>';
     const cantonActual = '<?= $nino['canton'] ?>';
     const parroquiaActual = '<?= $nino['parroquia'] ?>';

     // Cargar provincias al cargar la página
     document.addEventListener('DOMContentLoaded', function() {
       cargarProvincias();
     });

     // Función para cargar provincias
     function cargarProvincias() {
       fetch('../../ajax/ubicacion?action=provincias')
         .then(response => response.json())
         .then(data => {
           const selectProvincia = document.getElementById('provincia');
           data.forEach(provincia => {
             const option = document.createElement('option');
             option.value = provincia;
             option.textContent = provincia;
             if (provincia === provinciaActual) {
               option.selected = true;
             }
             selectProvincia.appendChild(option);
           });
           
           // Si hay provincia actual, cargar cantones
           if (provinciaActual) {
             cargarCantones(provinciaActual);
           }
         })
         .catch(error => console.error('Error:', error));
     }

     // Función para cargar cantones
     function cargarCantones(provincia) {
       const selectCanton = document.getElementById('canton');
       const selectParroquia = document.getElementById('parroquia');
       
       // Limpiar cantones y parroquias
       selectCanton.innerHTML = '<option value="">Seleccione un cantón</option>';
       selectParroquia.innerHTML = '<option value="">Seleccione una parroquia</option>';
       selectParroquia.disabled = true;
       
       if (provincia) {
         fetch(`../../ajax/ubicacion?action=cantones&provincia=${encodeURIComponent(provincia)}`)
           .then(response => response.json())
           .then(data => {
             data.forEach(canton => {
               const option = document.createElement('option');
               option.value = canton;
               option.textContent = canton;
               if (canton === cantonActual) {
                 option.selected = true;
               }
               selectCanton.appendChild(option);
             });
             selectCanton.disabled = false;
             
             // Si hay cantón actual, cargar parroquias
             if (cantonActual) {
               cargarParroquias(provincia, cantonActual);
             }
           })
           .catch(error => console.error('Error:', error));
       } else {
         selectCanton.disabled = true;
       }
     }

     // Función para cargar parroquias
     function cargarParroquias(provincia, canton) {
       const selectParroquia = document.getElementById('parroquia');
       
       // Limpiar parroquias
       selectParroquia.innerHTML = '<option value="">Seleccione una parroquia</option>';
       
       if (provincia && canton) {
         fetch(`../../ajax/ubicacion?action=parroquias&provincia=${encodeURIComponent(provincia)}&canton=${encodeURIComponent(canton)}`)
           .then(response => response.json())
           .then(data => {
             data.forEach(parroquia => {
               const option = document.createElement('option');
               option.value = parroquia;
               option.textContent = parroquia;
               if (parroquia === parroquiaActual) {
                 option.selected = true;
               }
               selectParroquia.appendChild(option);
             });
             selectParroquia.disabled = false;
           })
           .catch(error => console.error('Error:', error));
       } else {
         selectParroquia.disabled = true;
       }
     }

     // Event listeners para los selects
     document.getElementById('provincia').addEventListener('change', function() {
       cargarCantones(this.value);
     });

     document.getElementById('canton').addEventListener('change', function() {
       const provincia = document.getElementById('provincia').value;
       cargarParroquias(provincia, this.value);
     });
   </script>
  <script>
    // Datos actuales del niño
    const provinciaActual = '<?= $nino['provincia'] ?>';
    const cantonActual = '<?= $nino['canton'] ?>';
    const parroquiaActual = '<?= $nino['parroquia'] ?>';

    // Cargar provincias al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
      cargarProvincias();
      initializeFormValidation();
    });

    // Función para cargar provincias
    function cargarProvincias() {
      fetch('../../ajax/ubicacion?action=provincias')
        .then(response => response.json())
        .then(data => {
          const selectProvincia = document.getElementById('provincia');
          data.forEach(provincia => {
            const option = document.createElement('option');
            option.value = provincia;
            option.textContent = provincia;
            if (provincia === provinciaActual) {
              option.selected = true;
            }
            selectProvincia.appendChild(option);
          });
          
          // Si hay provincia actual, cargar cantones
          if (provinciaActual) {
            cargarCantones(provinciaActual);
          }
        })
        .catch(error => console.error('Error:', error));
    }

    // Función para cargar cantones
    function cargarCantones(provincia) {
      const selectCanton = document.getElementById('canton');
      const selectParroquia = document.getElementById('parroquia');
      
      // Limpiar cantones y parroquias
      selectCanton.innerHTML = '<option value="">Seleccione un cantón</option>';
      selectParroquia.innerHTML = '<option value="">Seleccione una parroquia</option>';
      selectParroquia.disabled = true;
      
      if (provincia) {
        fetch(`../../ajax/ubicacion?action=cantones&provincia=${encodeURIComponent(provincia)}`)
          .then(response => response.json())
          .then(data => {
            data.forEach(canton => {
              const option = document.createElement('option');
              option.value = canton;
              option.textContent = canton;
              if (canton === cantonActual) {
                option.selected = true;
              }
              selectCanton.appendChild(option);
            });
            selectCanton.disabled = false;
            
            // Si hay cantón actual, cargar parroquias
            if (cantonActual) {
              cargarParroquias(provincia, cantonActual);
            }
          })
          .catch(error => console.error('Error:', error));
      } else {
        selectCanton.disabled = true;
      }
    }

    // Función para cargar parroquias
    function cargarParroquias(provincia, canton) {
      const selectParroquia = document.getElementById('parroquia');
      
      // Limpiar parroquias
      selectParroquia.innerHTML = '<option value="">Seleccione una parroquia</option>';
      
      if (provincia && canton) {
        fetch(`../../ajax/ubicacion?action=parroquias&provincia=${encodeURIComponent(provincia)}&canton=${encodeURIComponent(canton)}`)
          .then(response => response.json())
          .then(data => {
            data.forEach(parroquia => {
              const option = document.createElement('option');
              option.value = parroquia;
              option.textContent = parroquia;
              if (parroquia === parroquiaActual) {
                option.selected = true;
              }
              selectParroquia.appendChild(option);
            });
            selectParroquia.disabled = false;
          })
          .catch(error => console.error('Error:', error));
      } else {
        selectParroquia.disabled = true;
      }
    }

    // Event listeners para los selects
    document.getElementById('provincia').addEventListener('change', function() {
      cargarCantones(this.value);
    });

    document.getElementById('canton').addEventListener('change', function() {
      const provincia = document.getElementById('provincia').value;
      cargarParroquias(provincia, this.value);
    });

    // Función para seleccionar radio buttons
    function selectRadio(name, value) {
      const radioItems = document.querySelectorAll(`input[name="${name}"]`);
      radioItems.forEach(radio => {
        const item = radio.closest('.radio-item');
        item.classList.remove('selected');
        if (radio.value === value) {
          radio.checked = true;
          item.classList.add('selected');
        }
      });
    }

    // Validación en tiempo real
    function initializeFormValidation() {
      // Validación de cédula
      document.getElementById('ciNiño').addEventListener('input', function() {
        validateCI(this);
      });

      document.getElementById('ciRepresentante1').addEventListener('input', function() {
        validateCI(this);
      });

      // Validación de teléfono
      document.getElementById('telefono1').addEventListener('input', function() {
        validatePhone(this);
      });

      document.getElementById('telefono2').addEventListener('input', function() {
        validatePhone(this);
      });

      // Validación de campos requeridos
      const requiredFields = ['nombreNiño', 'fechaNacimiento', 'provincia', 'canton', 'representante1', 'parentesco1'];
      requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
          field.addEventListener('blur', function() {
            validateRequired(this);
          });
        }
      });
    }

    function validateCI(input) {
      const value = input.value.replace(/\D/g, '');
      const isValid = /^\d{10}$/.test(value);
      
      if (value.length > 0) {
        if (isValid) {
          input.classList.remove('error');
          input.classList.add('success');
          hideError(input.id + 'Error');
        } else {
          input.classList.remove('success');
          input.classList.add('error');
          showError(input.id + 'Error');
        }
      } else {
        input.classList.remove('error', 'success');
        hideError(input.id + 'Error');
      }
    }

    function validatePhone(input) {
      const value = input.value.replace(/\D/g, '');
      const isValid = /^\d{7,10}$/.test(value);
      
      if (value.length > 0) {
        if (isValid) {
          input.classList.remove('error');
          input.classList.add('success');
          hideError(input.id + 'Error');
        } else {
          input.classList.remove('success');
          input.classList.add('error');
          showError(input.id + 'Error');
        }
      } else {
        input.classList.remove('error', 'success');
        hideError(input.id + 'Error');
      }
    }

    function validateRequired(input) {
      const isValid = input.value.trim().length > 0;
      
      if (isValid) {
        input.classList.remove('error');
        input.classList.add('success');
        hideError(input.id + 'Error');
      } else {
        input.classList.remove('success');
        input.classList.add('error');
        showError(input.id + 'Error');
      }
    }

    function showError(errorId) {
      const errorElement = document.getElementById(errorId);
      if (errorElement) {
        errorElement.style.display = 'flex';
      }
    }

    function hideError(errorId) {
      const errorElement = document.getElementById(errorId);
      if (errorElement) {
        errorElement.style.display = 'none';
      }
    }

    // Función para mostrar ayuda
    function showHelp() {
      alert('Ayuda para editar registro:\n\n' +
            '• Todos los campos marcados con * son obligatorios\n' +
            '• La cédula debe tener exactamente 10 dígitos\n' +
            '• El teléfono debe tener entre 7 y 10 dígitos\n' +
            '• Seleccione primero la provincia, luego el cantón\n' +
            '• Los campos de ubicación se cargan automáticamente\n' +
            '• Puede agregar un segundo representante si es necesario');
    }

    // Validación del formulario al enviar
    document.getElementById('editarForm').addEventListener('submit', function(e) {
      let hasErrors = false;
      
      // Validar campos requeridos
      const requiredFields = [
        { id: 'ciNiño', pattern: /^\d{10}$/, message: 'La cédula debe tener exactamente 10 dígitos' },
        { id: 'nombreNiño', pattern: /.+/, message: 'Este campo es obligatorio' },
        { id: 'fechaNacimiento', pattern: /.+/, message: 'Seleccione una fecha válida' },
        { id: 'provincia', pattern: /.+/, message: 'Este campo es obligatorio' },
        { id: 'canton', pattern: /.+/, message: 'Este campo es obligatorio' },
        { id: 'representante1', pattern: /.+/, message: 'Este campo es obligatorio' },
        { id: 'ciRepresentante1', pattern: /^\d{10}$/, message: 'La cédula debe tener exactamente 10 dígitos' },
        { id: 'parentesco1', pattern: /.+/, message: 'Este campo es obligatorio' },
        { id: 'telefono1', pattern: /^\d{7,10}$/, message: 'Ingrese un teléfono válido (7-10 dígitos)' }
      ];

      requiredFields.forEach(field => {
        const input = document.getElementById(field.id);
        if (input && !field.pattern.test(input.value)) {
          input.classList.add('error');
          showError(field.id + 'Error');
          hasErrors = true;
        }
      });

      // Validar radio buttons
      const radioGroups = ['sexo', 'activo', 'discapacidad'];
      radioGroups.forEach(group => {
        const checked = document.querySelector(`input[name="${group}"]:checked`);
        if (!checked) {
          showError(group + 'Error');
          hasErrors = true;
        }
      });

      if (hasErrors) {
        e.preventDefault();
        // Scroll al primer error
        const firstError = document.querySelector('.form-input.error, .radio-group');
        if (firstError) {
          firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }
    });
  </script>
</body>
</html>
