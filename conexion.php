<?php
/**
 * Sistema de Registro de Niños - Archivo de Conexión (Compatibilidad)
 * 
 * Este archivo mantiene la compatibilidad con el código existente
 * mientras se migra a la nueva estructura organizada.
 * 
 * @author Sistema Susana
 * @version 1.0.0
 * @since 2024
 * @deprecated Use config/database.php en su lugar
 */

// Incluir el nuevo archivo de configuración
require_once 'config/database.php';

// Mantener compatibilidad con el código existente
$servername = $db_config['host'];
$username = $db_config['username'];
$password = $db_config['password'];
$dbname = $db_config['database'];

// Obtener conexión bajo demanda
$conn = obtenerConexion();

// Si no hay conexión, mostrar mensaje de error amigable
if ($conn === null) {
    // En lugar de morir, establecer una conexión mock para compatibilidad
    $conn = new stdClass();
    $conn->connect_error = "Conexión a base de datos no disponible";
    $conn->error = "Base de datos no disponible";
}
?>
