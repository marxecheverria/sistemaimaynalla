<?php
include '../../verificar_sesion.php';
include '../../conexion.php';

// Validar que todos los campos requeridos estén presentes
$required_fields = ['ciNiño', 'nombreNiño', 'fechaNacimiento', 'sexo', 'provincia', 'canton', 'activo', 'discapacidad', 'representante1', 'ciRepresentante1', 'parentesco1', 'telefono1'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        die("Error: El campo $field es obligatorio");
    }
}

// Recibir y sanitizar datos del formulario
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

// Validar formato de C.I.
if (!preg_match('/^\d{10}$/', $ci_nino)) {
    die("Error: El C.I. del niño debe tener exactamente 10 dígitos");
}

// Validar formato de C.I. del representante
$ciRep1 = $conn->real_escape_string(trim($_POST['ciRepresentante1']));
if (!preg_match('/^\d{10}$/', $ciRep1)) {
    die("Error: El C.I. del representante debe tener exactamente 10 dígitos");
}

// Validar formato de teléfono
$telefono1 = $conn->real_escape_string(trim($_POST['telefono1']));
if (!preg_match('/^\d{7,10}$/', $telefono1)) {
    die("Error: El teléfono debe tener entre 7 y 10 dígitos");
}

// Insertar niño usando prepared statement
$sql_nino = "INSERT INTO ninos (ci_nino, nombre_completo, fecha_nacimiento, sexo, provincia, canton, parroquia, barrio, direccion, estudiante_activo, grado, discapacitado, detalle_discapacidad) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql_nino);
$stmt->bind_param("sssssssssssss", $ci_nino, $nombre_nino, $fecha_nacimiento, $sexo, $provincia, $canton, $parroquia, $barrio, $direccion, $activo, $grado, $discapacidad, $detalle_discapacidad);

if ($stmt->execute()) {
    $id_nino = $conn->insert_id;

    // Representante 1
    $representante1 = $conn->real_escape_string(trim($_POST['representante1']));
    $parentesco1 = $conn->real_escape_string(trim($_POST['parentesco1']));

    $sql_rep1 = "INSERT INTO representantes (id_nino, nombre, ci, parentesco, telefono, representante_numero) 
                 VALUES (?, ?, ?, ?, ?, '1')";
    $stmt_rep1 = $conn->prepare($sql_rep1);
    $stmt_rep1->bind_param("issss", $id_nino, $representante1, $ciRep1, $parentesco1, $telefono1);
    $stmt_rep1->execute();

    // Representante 2 (opcional)
    if (!empty($_POST['representante2']) || !empty($_POST['telefono2'])) {
        $representante2 = $conn->real_escape_string(trim($_POST['representante2']));
        $telefono2 = $conn->real_escape_string(trim($_POST['telefono2']));
        
        // Validar formato de teléfono 2 si se proporciona
        if (!empty($telefono2) && !preg_match('/^\d{7,10}$/', $telefono2)) {
            die("Error: El teléfono del segundo representante debe tener entre 7 y 10 dígitos");
        }
        
        $sql_rep2 = "INSERT INTO representantes (id_nino, nombre, telefono, representante_numero) 
                     VALUES (?, ?, ?, '2')";
        $stmt_rep2 = $conn->prepare($sql_rep2);
        $stmt_rep2->bind_param("iss", $id_nino, $representante2, $telefono2);
        $stmt_rep2->execute();
    }

    // Redirigir al panel con mensaje de éxito
    header("Location: panel.php?success=registro_guardado");
    exit();
    
} else {
    // Redirigir al panel con mensaje de error
    header("Location: panel.php?error=error_guardado");
    exit();
}

$conn->close();
?>
