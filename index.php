<?php
/**
 * Sistema de Registro de Niños - Archivo Principal de Entrada
 * 
 * Este archivo actúa como punto de entrada principal del sistema,
 * redirigiendo las peticiones a los archivos correspondientes según
 * la nueva estructura organizada.
 * 
 * @author Sistema Susana
 * @version 1.0.0
 * @since 2024
 */

// Incluir configuración de rutas
require_once 'config/routes.php';

// Obtener la ruta solicitada
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remover el directorio base si existe
$base_path = dirname($script_name);
if ($base_path !== '/') {
    $path = substr($path, strlen($base_path));
}

// Limpiar la ruta
$path = trim($path, '/');

// Mapeo de rutas
$routes = [
    '' => 'public/index.html',
    'index.html' => 'public/index.html',
    'login' => 'src/auth/login.php',
    'auth' => 'src/auth/auth.php',
    'panel' => 'src/pages/panel.php',
    'estadisticas' => 'src/pages/estadisticas.php',
    'reportes' => 'src/pages/reportes.php',
    'registro' => 'public/registro.html',
    'nuevo-registro' => 'public/registro.html',
    'editar' => 'src/pages/editar.php',
    'ver' => 'src/pages/ver.php',
    'guardar-registro' => 'src/pages/guardar_registro.php',
    'admin/usuarios' => 'src/admin/crear_usuarios.php',
    'ajax/ubicacion' => 'src/api/ajax_ubicacion.php'
];

// Determinar qué archivo cargar
$file_to_load = null;

if (isset($routes[$path])) {
    $file_to_load = $routes[$path];
} else {
    // Buscar archivos PHP directamente
    $possible_files = [
        "src/pages/{$path}.php",
        "src/auth/{$path}.php",
        "src/admin/{$path}.php",
        "public/{$path}.html",
        "public/{$path}.php"
    ];
    
    foreach ($possible_files as $file) {
        if (file_exists($file)) {
            $file_to_load = $file;
            break;
        }
    }
}

// Cargar el archivo correspondiente
if ($file_to_load && file_exists($file_to_load)) {
    // Configurar headers según el tipo de archivo
    $extension = pathinfo($file_to_load, PATHINFO_EXTENSION);
    
    switch ($extension) {
        case 'html':
            header('Content-Type: text/html; charset=utf-8');
            break;
        case 'css':
            header('Content-Type: text/css; charset=utf-8');
            break;
        case 'js':
            header('Content-Type: application/javascript; charset=utf-8');
            break;
        case 'php':
            // Los headers se manejan en el archivo PHP
            break;
    }
    
    // Incluir el archivo
    include $file_to_load;
} else {
    // Página no encontrada
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Página No Encontrada</title>
        <link rel="stylesheet" href="public/assets/css/estilos-corporativos.css">
        <link rel="stylesheet" href="public/assets/css/menu.css">
    </head>
    <body>
        <div style="text-align: center; padding: 100px 20px;">
            <h1 style="color: #1c2c50; font-size: 4em; margin-bottom: 20px;">404</h1>
            <h2 style="color: #dda619; margin-bottom: 30px;">Página No Encontrada</h2>
            <p style="font-size: 1.2em; color: #6c757d; margin-bottom: 40px;">
                La página que buscas no existe o ha sido movida.
            </p>
            <a href="/" class="btn btn-primary">
                <span style="margin-right: 8px;">🏠</span>
                Volver al Inicio
            </a>
        </div>
    </body>
    </html>
    <?php
}
?>
