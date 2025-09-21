/**
 * Sistema de Registro de Niños - Configuración de Rutas
 * 
 * Este archivo centraliza todas las rutas del sistema para facilitar
 * el mantenimiento y la organización de archivos.
 * 
 * @author Sistema Susana
 * @version 1.0.0
 * @since 2024
 */

// Definir rutas base del sistema
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ASSETS_PATH', PUBLIC_PATH . '/assets');
define('CSS_PATH', ASSETS_PATH . '/css');
define('JS_PATH', ASSETS_PATH . '/js');
define('IMAGES_PATH', ASSETS_PATH . '/images');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('CACHE_PATH', ROOT_PATH . '/cache');

// URLs públicas
define('BASE_URL', '/');
define('ASSETS_URL', BASE_URL . 'public/assets/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMAGES_URL', ASSETS_URL . 'images/');
define('UPLOADS_URL', BASE_URL . 'public/uploads/');

// Rutas de archivos PHP
define('AUTH_PATH', SRC_PATH . '/auth');
define('PAGES_PATH', SRC_PATH . '/pages');
define('API_PATH', SRC_PATH . '/api');
define('ADMIN_PATH', SRC_PATH . '/admin');

// URLs de páginas
define('LOGIN_URL', BASE_URL . 'login');
define('PANEL_URL', BASE_URL . 'panel');
define('REGISTRO_URL', BASE_URL . 'registro');
define('ESTADISTICAS_URL', BASE_URL . 'estadisticas');
define('REPORTES_URL', BASE_URL . 'reportes');
define('DATOS_DETALLADOS_URL', BASE_URL . 'datos-detallados');
define('ADMIN_USUARIOS_URL', BASE_URL . 'admin/usuarios');
define('LOGOUT_URL', BASE_URL . 'auth/logout');

/**
 * Función para obtener la URL completa de un archivo CSS
 */
function css_url($file) {
    return CSS_URL . $file;
}

/**
 * Función para obtener la URL completa de un archivo JS
 */
function js_url($file) {
    return JS_URL . $file;
}

/**
 * Función para obtener la URL completa de una imagen
 */
function image_url($file) {
    return IMAGES_URL . $file;
}

/**
 * Función para obtener la URL completa de un archivo subido
 */
function upload_url($file) {
    return UPLOADS_URL . $file;
}

/**
 * Función para incluir un archivo PHP de forma segura
 */
function include_src($file) {
    $path = SRC_PATH . '/' . $file;
    if (file_exists($path)) {
        return include $path;
    }
    throw new Exception("Archivo no encontrado: $file");
}

/**
 * Función para incluir un archivo de configuración
 */
function include_config($file) {
    $path = CONFIG_PATH . '/' . $file;
    if (file_exists($path)) {
        return include $path;
    }
    throw new Exception("Archivo de configuración no encontrado: $file");
}

/**
 * Función para incluir un archivo de funciones
 */
function include_functions($file) {
    $path = INCLUDES_PATH . '/' . $file;
    if (file_exists($path)) {
        return include $path;
    }
    throw new Exception("Archivo de funciones no encontrado: $file");
}

/**
 * Función para obtener la ruta completa de un archivo
 */
function get_file_path($type, $file) {
    switch ($type) {
        case 'css':
            return CSS_PATH . '/' . $file;
        case 'js':
            return JS_PATH . '/' . $file;
        case 'image':
            return IMAGES_PATH . '/' . $file;
        case 'upload':
            return UPLOADS_PATH . '/' . $file;
        case 'src':
            return SRC_PATH . '/' . $file;
        case 'config':
            return CONFIG_PATH . '/' . $file;
        case 'includes':
            return INCLUDES_PATH . '/' . $file;
        default:
            throw new Exception("Tipo de archivo no válido: $type");
    }
}

/**
 * Función para verificar si un archivo existe
 */
function file_exists_type($type, $file) {
    return file_exists(get_file_path($type, $file));
}

/**
 * Función para crear directorios necesarios
 */
function create_directories() {
    $directories = [
        CSS_PATH,
        JS_PATH,
        IMAGES_PATH,
        UPLOADS_PATH,
        LOGS_PATH,
        CACHE_PATH,
        AUTH_PATH,
        PAGES_PATH,
        API_PATH,
        ADMIN_PATH
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Crear directorios automáticamente
create_directories();
?>
