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

// La conexión ya está establecida en config/database.php
// $conn está disponible globalmente
?>
