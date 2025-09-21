<?php
/**
 * Sistema de Registro de Niños - Funciones de Utilidad
 * 
 * Este archivo contiene funciones de utilidad comunes para todo el sistema,
 * incluyendo validaciones, formateo de datos y operaciones de base de datos.
 * 
 * @author Sistema Susana
 * @version 1.0.0
 * @since 2024
 */

// Los archivos de configuración se incluyen solo cuando son necesarios
// require_once 'config/database.php';
// require_once 'config/auth.php';

/**
 * Clase para manejo de respuestas del sistema
 */
class RespuestaSistema {
    private $success;
    private $message;
    private $data;
    
    public function __construct($success = true, $message = '', $data = null) {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
    }
    
    public function enviar() {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    public function enviarHTML() {
        if ($this->success) {
            echo "<div class='alert alert-success'>{$this->message}</div>";
        } else {
            echo "<div class='alert alert-danger'>{$this->message}</div>";
        }
    }
}

/**
 * Clase para manejo de validaciones
 */
class Validador {
    
    /**
     * Valida datos de un niño
     * 
     * @param array $datos Datos del niño a validar
     * @return array Errores encontrados
     */
    public static function validarNino($datos) {
        $errores = [];
        
        // Validar cédula
        if (empty($datos['ci_nino'])) {
            $errores['ci_nino'] = 'La cédula es obligatoria';
        } elseif (!validarCedula($datos['ci_nino'])) {
            $errores['ci_nino'] = 'La cédula no tiene un formato válido';
        }
        
        // Validar nombre
        if (empty($datos['nombre_completo'])) {
            $errores['nombre_completo'] = 'El nombre completo es obligatorio';
        } elseif (strlen($datos['nombre_completo']) < 3) {
            $errores['nombre_completo'] = 'El nombre debe tener al menos 3 caracteres';
        }
        
        // Validar fecha de nacimiento
        if (empty($datos['fecha_nacimiento'])) {
            $errores['fecha_nacimiento'] = 'La fecha de nacimiento es obligatoria';
        } else {
            $edad = calcularEdad($datos['fecha_nacimiento']);
            if ($edad < 0 || $edad > 25) {
                $errores['fecha_nacimiento'] = 'La edad debe estar entre 0 y 25 años';
            }
        }
        
        // Validar sexo
        if (empty($datos['sexo']) || !in_array($datos['sexo'], ['Masculino', 'Femenino'])) {
            $errores['sexo'] = 'Debe seleccionar un sexo válido';
        }
        
        // Validar provincia
        if (empty($datos['provincia'])) {
            $errores['provincia'] = 'La provincia es obligatoria';
        }
        
        // Validar cantón
        if (empty($datos['canton'])) {
            $errores['canton'] = 'El cantón es obligatorio';
        }
        
        return $errores;
    }
    
    /**
     * Valida datos de un representante
     * 
     * @param array $datos Datos del representante a validar
     * @return array Errores encontrados
     */
    public static function validarRepresentante($datos) {
        $errores = [];
        
        // Validar nombre
        if (empty($datos['nombre'])) {
            $errores['nombre'] = 'El nombre del representante es obligatorio';
        }
        
        // Validar cédula (opcional para representante 2)
        if (!empty($datos['ci']) && !validarCedula($datos['ci'])) {
            $errores['ci'] = 'La cédula no tiene un formato válido';
        }
        
        // Validar teléfono
        if (!empty($datos['telefono']) && !validarTelefono($datos['telefono'])) {
            $errores['telefono'] = 'El teléfono no tiene un formato válido';
        }
        
        return $errores;
    }
}

/**
 * Clase para operaciones de base de datos
 */
class OperacionesDB {
    private $conn;
    
    public function __construct($conexion) {
        $this->conn = $conexion;
    }
    
    /**
     * Obtiene estadísticas por sexo
     * 
     * @return array Estadísticas por sexo
     */
    public function obtenerEstadisticasSexo() {
        $sql = "SELECT sexo, COUNT(*) as total FROM ninos GROUP BY sexo";
        $result = $this->conn->query($sql);
        
        $datos = [];
        while ($row = $result->fetch_assoc()) {
            $datos[$row['sexo']] = $row['total'];
        }
        
        return $datos;
    }
    
    /**
     * Obtiene estadísticas por provincia
     * 
     * @return array Estadísticas por provincia
     */
    public function obtenerEstadisticasProvincia() {
        $sql = "SELECT provincia, COUNT(*) as total FROM ninos GROUP BY provincia ORDER BY total DESC";
        $result = $this->conn->query($sql);
        
        $datos = [];
        while ($row = $result->fetch_assoc()) {
            $datos[$row['provincia']] = $row['total'];
        }
        
        return $datos;
    }
    
    /**
     * Obtiene estadísticas por rango de edad
     * 
     * @return array Estadísticas por edad
     */
    public function obtenerEstadisticasEdad() {
        $sql = "SELECT 
            CASE 
                WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 0 AND 5 THEN '0-5 años'
                WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 6 AND 10 THEN '6-10 años'
                WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 11 AND 15 THEN '11-15 años'
                WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 16 AND 18 THEN '16-18 años'
                ELSE '18+ años'
            END as rango_edad,
            COUNT(*) as total
            FROM ninos 
            GROUP BY rango_edad 
            ORDER BY 
                CASE 
                    WHEN rango_edad = '0-5 años' THEN 1
                    WHEN rango_edad = '6-10 años' THEN 2
                    WHEN rango_edad = '11-15 años' THEN 3
                    WHEN rango_edad = '16-18 años' THEN 4
                    ELSE 5
                END";
        
        $result = $this->conn->query($sql);
        
        $datos = [];
        while ($row = $result->fetch_assoc()) {
            $datos[$row['rango_edad']] = $row['total'];
        }
        
        return $datos;
    }
    
    /**
     * Obtiene estadísticas por estado académico
     * 
     * @return array Estadísticas por estado académico
     */
    public function obtenerEstadisticasEstadoAcademico() {
        $sql = "SELECT estudiante_activo, COUNT(*) as total FROM ninos GROUP BY estudiante_activo";
        $result = $this->conn->query($sql);
        
        $datos = [];
        while ($row = $result->fetch_assoc()) {
            $datos[$row['estudiante_activo']] = $row['total'];
        }
        
        return $datos;
    }
    
    /**
     * Obtiene estadísticas por discapacidad
     * 
     * @return array Estadísticas por discapacidad
     */
    public function obtenerEstadisticasDiscapacidad() {
        $sql = "SELECT discapacitado, COUNT(*) as total FROM ninos GROUP BY discapacitado";
        $result = $this->conn->query($sql);
        
        $datos = [];
        while ($row = $result->fetch_assoc()) {
            $datos[$row['discapacitado']] = $row['total'];
        }
        
        return $datos;
    }
    
    /**
     * Obtiene el total de niños registrados
     * 
     * @return int Total de niños
     */
    public function obtenerTotalNinos() {
        $sql = "SELECT COUNT(*) as total FROM ninos";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['total'];
    }
    
    /**
     * Obtiene datos detallados de todos los niños
     * 
     * @param int $limite Límite de registros
     * @param int $offset Offset para paginación
     * @return array Datos detallados
     */
    public function obtenerDatosDetallados($limite = null, $offset = 0) {
        $sql = "SELECT 
            n.id,
            n.ci_nino,
            n.nombre_completo,
            n.fecha_nacimiento,
            TIMESTAMPDIFF(YEAR, n.fecha_nacimiento, CURDATE()) as edad,
            n.sexo,
            n.provincia,
            n.canton,
            n.parroquia,
            n.barrio,
            n.direccion,
            n.estudiante_activo,
            n.grado,
            n.discapacitado,
            n.detalle_discapacidad,
            n.fecha_registro,
            r1.nombre as rep1_nombre,
            r1.ci as rep1_ci,
            r1.parentesco as rep1_parentesco,
            r1.telefono as rep1_telefono,
            r2.nombre as rep2_nombre,
            r2.telefono as rep2_telefono
        FROM ninos n
        LEFT JOIN representantes r1 ON n.id = r1.id_nino AND r1.representante_numero = '1'
        LEFT JOIN representantes r2 ON n.id = r2.id_nino AND r2.representante_numero = '2'
        ORDER BY n.fecha_registro DESC";
        
        if ($limite) {
            $sql .= " LIMIT $limite OFFSET $offset";
        }
        
        $result = $this->conn->query($sql);
        
        $datos = [];
        while ($row = $result->fetch_assoc()) {
            $datos[] = $row;
        }
        
        return $datos;
    }
}

/**
 * Clase para manejo de archivos
 */
class ManejadorArchivos {
    
    /**
     * Valida un archivo subido
     * 
     * @param array $archivo Array $_FILES del archivo
     * @return array Resultado de la validación
     */
    public static function validarArchivo($archivo) {
        $errores = [];
        
        // Verificar si hay error en la subida
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $errores[] = 'Error al subir el archivo';
            return $errores;
        }
        
        // Verificar tamaño
        if ($archivo['size'] > TAMANO_MAXIMO_ARCHIVO) {
            $errores[] = 'El archivo es demasiado grande';
        }
        
        // Verificar tipo de archivo
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ARCHIVOS_PERMITIDOS)) {
            $errores[] = 'Tipo de archivo no permitido';
        }
        
        return $errores;
    }
    
    /**
     * Sube un archivo al servidor
     * 
     * @param array $archivo Array $_FILES del archivo
     * @param string $directorio Directorio de destino
     * @return string|false Nombre del archivo subido o false en caso de error
     */
    public static function subirArchivo($archivo, $directorio = 'uploads/') {
        $errores = self::validarArchivo($archivo);
        
        if (!empty($errores)) {
            return false;
        }
        
        // Crear directorio si no existe
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        // Generar nombre único
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $nombreArchivo = uniqid() . '.' . $extension;
        $rutaCompleta = $directorio . $nombreArchivo;
        
        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return $nombreArchivo;
        }
        
        return false;
    }
}

/**
 * Clase para formateo de datos
 */
class Formateador {
    
    /**
     * Formatea una fecha para mostrar
     * 
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @param string $formato Formato de salida
     * @return string Fecha formateada
     */
    public static function formatearFecha($fecha, $formato = 'd/m/Y') {
        if (empty($fecha)) {
            return 'N/A';
        }
        
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
        return $fechaObj ? $fechaObj->format($formato) : 'N/A';
    }
    
    /**
     * Formatea un número de teléfono
     * 
     * @param string $telefono Número de teléfono
     * @return string Teléfono formateado
     */
    public static function formatearTelefono($telefono) {
        if (empty($telefono)) {
            return 'N/A';
        }
        
        // Remover caracteres no numéricos
        $telefono = preg_replace('/[^0-9]/', '', $telefono);
        
        // Formatear según longitud
        if (strlen($telefono) === 10) {
            return substr($telefono, 0, 3) . '-' . substr($telefono, 3, 3) . '-' . substr($telefono, 6);
        } elseif (strlen($telefono) === 9) {
            return substr($telefono, 0, 2) . '-' . substr($telefono, 2, 3) . '-' . substr($telefono, 5);
        }
        
        return $telefono;
    }
    
    /**
     * Formatea una cédula
     * 
     * @param string $cedula Cédula
     * @return string Cédula formateada
     */
    public static function formatearCedula($cedula) {
        if (empty($cedula)) {
            return 'N/A';
        }
        
        // Remover caracteres no numéricos
        $cedula = preg_replace('/[^0-9]/', '', $cedula);
        
        // Formatear cédula ecuatoriana
        if (strlen($cedula) === 10) {
            return substr($cedula, 0, 2) . '.' . substr($cedula, 2, 6) . '.' . substr($cedula, 8);
        }
        
        return $cedula;
    }
}

/**
 * Función para generar breadcrumbs
 * 
 * @param array $breadcrumbs Array de breadcrumbs
 * @return string HTML de breadcrumbs
 */
function generarBreadcrumbs($breadcrumbs) {
    $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    
    foreach ($breadcrumbs as $index => $breadcrumb) {
        $isLast = $index === count($breadcrumbs) - 1;
        
        if ($isLast) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . $breadcrumb['texto'] . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="' . $breadcrumb['url'] . '">' . $breadcrumb['texto'] . '</a></li>';
        }
    }
    
    $html .= '</ol></nav>';
    return $html;
}

/**
 * Función para generar paginación
 * 
 * @param int $paginaActual Página actual
 * @param int $totalPaginas Total de páginas
 * @param string $urlBase URL base para los enlaces
 * @return string HTML de paginación
 */
function generarPaginacion($paginaActual, $totalPaginas, $urlBase) {
    if ($totalPaginas <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Paginación"><ul class="pagination justify-content-center">';
    
    // Botón anterior
    if ($paginaActual > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $urlBase . '?pagina=' . ($paginaActual - 1) . '">Anterior</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Anterior</span></li>';
    }
    
    // Números de página
    $inicio = max(1, $paginaActual - 2);
    $fin = min($totalPaginas, $paginaActual + 2);
    
    for ($i = $inicio; $i <= $fin; $i++) {
        $activa = $i === $paginaActual ? 'active' : '';
        $html .= '<li class="page-item ' . $activa . '"><a class="page-link" href="' . $urlBase . '?pagina=' . $i . '">' . $i . '</a></li>';
    }
    
    // Botón siguiente
    if ($paginaActual < $totalPaginas) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $urlBase . '?pagina=' . ($paginaActual + 1) . '">Siguiente</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Siguiente</span></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Función para incluir configuración de base de datos solo cuando sea necesario
 */
function incluirConfiguracionDB() {
    static $incluido = false;
    if (!$incluido) {
        require_once 'config/database.php';
        $incluido = true;
    }
}

/**
 * Función para incluir configuración de autenticación solo cuando sea necesario
 */
function incluirConfiguracionAuth() {
    static $incluido = false;
    if (!$incluido) {
        require_once 'config/auth.php';
        $incluido = true;
    }
}
?>
