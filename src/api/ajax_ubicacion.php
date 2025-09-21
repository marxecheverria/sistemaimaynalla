<?php
// Archivo AJAX para cargar datos geográficos de Ecuador
header('Content-Type: application/json');
include '../../includes/datos_ecuador.php';

// Obtener parámetros
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'provincias':
            $provincias = array_keys($provincias_ecuador);
            echo json_encode($provincias);
            break;
            
        case 'cantones':
            $provincia = $_GET['provincia'] ?? '';
            if (empty($provincia)) {
                throw new Exception('Provincia no especificada');
            }
            
            if (!isset($provincias_ecuador[$provincia])) {
                throw new Exception('Provincia no encontrada');
            }
            
            $cantones = array_keys($provincias_ecuador[$provincia]);
            echo json_encode($cantones);
            break;
            
        case 'parroquias':
            $provincia = $_GET['provincia'] ?? '';
            $canton = $_GET['canton'] ?? '';
            
            if (empty($provincia) || empty($canton)) {
                throw new Exception('Provincia y cantón deben ser especificados');
            }
            
            if (!isset($provincias_ecuador[$provincia][$canton])) {
                throw new Exception('Cantón no encontrado');
            }
            
            $parroquias = $provincias_ecuador[$provincia][$canton];
            echo json_encode($parroquias);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>