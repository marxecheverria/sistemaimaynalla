<?php
/**
 * Sistema de Registro de Niños - Verificación de Sesión (Compatibilidad)
 * 
 * Este archivo mantiene la compatibilidad con el código existente
 * mientras se migra a la nueva estructura de autenticación.
 * 
 * @author Sistema Susana
 * @version 1.0.0
 * @since 2024
 * @deprecated Use config/auth.php en su lugar
 */

// Incluir el nuevo archivo de autenticación
require_once 'config/auth.php';

// Mantener compatibilidad con el código existente
// La función requerirAutenticacion() ya está disponible desde config/auth.php

// Función de compatibilidad para verificar rol
function verificarRol($roles_permitidos) {
    if (!isset($_SESSION['rol'])) {
        return false;
    }
    
    if (is_string($roles_permitidos)) {
        $roles_permitidos = [$roles_permitidos];
    }
    
    return in_array($_SESSION['rol'], $roles_permitidos);
}

// Función de compatibilidad para obtener usuario actual
function obtenerUsuarioActual() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'nombre_completo' => $_SESSION['nombre_completo'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'rol' => $_SESSION['rol'] ?? null,
        'login_time' => $_SESSION['login_time'] ?? null
    ];
}

// Función de compatibilidad para registrar actividad
function registrarActividad($accion, $detalles = '') {
    if (!isset($_SESSION['user_id'])) {
        return;
    }
    
    registrarLog($accion, $detalles, [
        'usuario_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? 'unknown'
    ]);
}

// Registrar acceso a la página actual
$pagina_actual = basename($_SERVER['PHP_SELF']);
registrarActividad('page_access', "Acceso a: " . $pagina_actual);
?>


