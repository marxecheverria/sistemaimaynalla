# 📊 Sistema de Registro de Niños - Susana

Un sistema web completo para el registro y gestión de información de niños, desarrollado con PHP, MySQL y tecnologías web modernas.

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Tecnologías Utilizadas](#-tecnologías-utilizadas)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Uso del Sistema](#-uso-del-sistema)
- [API y Funciones](#-api-y-funciones)
- [Base de Datos](#-base-de-datos)
- [Seguridad](#-seguridad)
- [Contribución](#-contribución)
- [Licencia](#-licencia)

## 🚀 Características

### ✨ Funcionalidades Principales
- **Registro de Niños**: Formulario completo con validaciones
- **Gestión de Representantes**: Soporte para hasta 2 representantes por niño
- **Estadísticas Avanzadas**: Gráficas interactivas y reportes
- **Sistema de Autenticación**: Login seguro con sesiones
- **Exportación de Datos**: PDF, Excel y CSV
- **Interfaz Responsive**: Diseño moderno y adaptable

### 📊 Módulos del Sistema
- **Panel Principal**: Vista general de todos los registros
- **Nuevo Registro**: Formulario de registro de niños
- **Edición**: Modificación de datos existentes
- **Estadísticas**: Análisis de datos con gráficas
- **Reportes**: Generación de documentos
- **Gestión de Usuarios**: Administración del sistema

## 🛠️ Tecnologías Utilizadas

### Backend
- **PHP 8.1+**: Lenguaje de programación principal
- **MySQL 8.0**: Base de datos relacional
- **PDO/MySQLi**: Conexión a base de datos

### Frontend
- **HTML5**: Estructura semántica
- **CSS3**: Estilos modernos con Flexbox/Grid
- **JavaScript ES6+**: Interactividad del cliente
- **Chart.js**: Gráficas interactivas
- **Bootstrap 5**: Framework CSS (opcional)

### Herramientas de Desarrollo
- **Git**: Control de versiones
- **Composer**: Gestión de dependencias PHP
- **PHPUnit**: Testing (futuro)

## 📁 Estructura del Proyecto

```
sistema-susana/
├── 📁 config/                 # Configuración del sistema
│   ├── database.php          # Configuración de BD
│   └── auth.php              # Sistema de autenticación
├── 📁 includes/              # Archivos de funciones comunes
│   └── functions.php         # Funciones de utilidad
├── 📁 imagenes/              # Recursos gráficos
│   ├── logo.png              # Logo del sistema
│   └── login.jpg             # Imagen de login
├── 📁 css/                   # Hojas de estilo
│   ├── estilos-corporativos.css
│   └── menu.css
├── 📁 js/                    # Archivos JavaScript
├── 📁 tablas/                # Scripts de base de datos
│   ├── tablas.sql            # Estructura principal
│   └── ninos.sql            # Datos de ejemplo
├── 📄 index.html             # Página de login
├── 📄 auth.php               # Autenticación
├── 📄 panel.php              # Panel principal
├── 📄 registro.html           # Formulario de registro
├── 📄 guardar_registro.php   # Procesamiento de registros
├── 📄 editar.php             # Edición de registros
├── 📄 ver.php                # Visualización de registros
├── 📄 estadisticas.php       # Página de estadísticas
├── 📄 reportes.php           # Generador de reportes
├── 📄 datos_detallados.php   # Vista detallada
├── 📄 conexion.php           # Conexión BD (compatibilidad)
├── 📄 verificar_sesion.php   # Verificación de sesión
└── 📄 README.md              # Este archivo
```

## ⚙️ Instalación

### Requisitos del Sistema
- **Servidor Web**: Apache 2.4+ o Nginx
- **PHP**: 8.1 o superior
- **MySQL**: 8.0 o superior
- **Extensiones PHP**: mysqli, pdo_mysql, json, session

### Pasos de Instalación

1. **Clonar el Repositorio**
   ```bash
   git clone https://github.com/tu-usuario/sistema-susana.git
   cd sistema-susana
   ```

2. **Configurar Base de Datos**
   ```sql
   -- Crear base de datos
   CREATE DATABASE sistema_ninos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   
   -- Importar estructura
   mysql -u usuario -p sistema_ninos < tablas/tablas.sql
   ```

3. **Configurar Conexión**
   ```php
   // Editar config/database.php
   $db_config = [
       'host' => 'localhost',
       'username' => 'tu_usuario',
       'password' => 'tu_password',
       'database' => 'sistema_ninos',
       'charset' => 'utf8mb4'
   ];
   ```

4. **Configurar Permisos**
   ```bash
   chmod 755 imagenes/
   chmod 644 *.php
   ```

## 🔧 Configuración

### Variables de Entorno
```php
// config/database.php
define('SISTEMA_VERSION', '1.0.0');
define('SISTEMA_NOMBRE', 'Sistema de Registro de Niños');
define('REGISTROS_POR_PAGINA', 10);
define('TAMANO_MAXIMO_ARCHIVO', 5 * 1024 * 1024); // 5MB
```

### Configuración de Seguridad
```php
// config/auth.php
// Tiempo de expiración de sesión (2 horas)
$tiempoExpiracion = 7200;

// Configuración de cookies seguras
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
```

## 📖 Uso del Sistema

### 1. Acceso al Sistema
- Navegar a `index.html`
- Ingresar credenciales de usuario
- El sistema redirige al panel principal

### 2. Registro de Niños
- Hacer clic en "Nuevo Registro"
- Completar formulario con datos del niño
- Agregar información de representantes
- Guardar registro

### 3. Gestión de Datos
- **Ver**: Consultar información completa
- **Editar**: Modificar datos existentes
- **Eliminar**: Remover registros (con confirmación)

### 4. Estadísticas y Reportes
- **Estadísticas**: Ver gráficas interactivas
- **Reportes**: Exportar datos en PDF/Excel
- **Datos Detallados**: Lista completa exportable

## 🔌 API y Funciones

### Clases Principales

#### `OperacionesDB`
```php
// Obtener estadísticas
$operacionesDB = new OperacionesDB($conn);
$estadisticas = $operacionesDB->obtenerEstadisticasSexo();
```

#### `Validador`
```php
// Validar datos de niño
$errores = Validador::validarNino($datos);
if (empty($errores)) {
    // Datos válidos
}
```

#### `RespuestaSistema`
```php
// Enviar respuesta JSON
$respuesta = new RespuestaSistema(true, 'Operación exitosa', $datos);
$respuesta->enviar();
```

### Funciones de Utilidad
```php
// Validaciones
validarCedula($cedula);        // Cédula ecuatoriana
validarTelefono($telefono);    // Teléfono ecuatoriano
calcularEdad($fechaNacimiento); // Edad en años

// Formateo
Formateador::formatearFecha($fecha);
Formateador::formatearTelefono($telefono);
Formateador::formatearCedula($cedula);

// Autenticación
requerirAutenticacion();       // Verificar login
requerirAdministrador();      // Verificar admin
generarTokenCSRF();           // Token de seguridad
```

## 🗄️ Base de Datos

### Estructura de Tablas

#### Tabla `ninos`
```sql
CREATE TABLE ninos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ci_nino VARCHAR(10) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    sexo ENUM('Masculino', 'Femenino') NOT NULL,
    provincia VARCHAR(50) NOT NULL,
    canton VARCHAR(50) NOT NULL,
    parroquia VARCHAR(50),
    barrio VARCHAR(50),
    direccion VARCHAR(150),
    estudiante_activo ENUM('Si','No') NOT NULL,
    grado VARCHAR(50),
    discapacitado ENUM('Si','No') NOT NULL,
    detalle_discapacidad TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Tabla `representantes`
```sql
CREATE TABLE representantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nino INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    ci VARCHAR(10),
    parentesco VARCHAR(50),
    telefono VARCHAR(15),
    representante_numero ENUM('1','2') NOT NULL,
    FOREIGN KEY (id_nino) REFERENCES ninos(id) ON DELETE CASCADE
);
```

### Consultas Principales

#### Estadísticas por Sexo
```sql
SELECT sexo, COUNT(*) as total 
FROM ninos 
GROUP BY sexo;
```

#### Estadísticas por Provincia
```sql
SELECT provincia, COUNT(*) as total 
FROM ninos 
GROUP BY provincia 
ORDER BY total DESC;
```

#### Estadísticas por Edad
```sql
SELECT 
    CASE 
        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 0 AND 5 THEN '0-5 años'
        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 6 AND 10 THEN '6-10 años'
        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 11 AND 15 THEN '11-15 años'
        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 16 AND 18 THEN '16-18 años'
        ELSE '18+ años'
    END as rango_edad,
    COUNT(*) as total
FROM ninos 
GROUP BY rango_edad;
```

## 🔒 Seguridad

### Medidas Implementadas

#### Autenticación y Autorización
- **Sesiones seguras**: Regeneración de ID, cookies HTTPOnly
- **Verificación de permisos**: Control de acceso por roles
- **Expiración de sesión**: Timeout automático por inactividad

#### Validación de Datos
- **Sanitización**: Escape de caracteres especiales
- **Validación de entrada**: Verificación de formatos
- **Prevención SQL Injection**: Uso de prepared statements

#### Protección CSRF
```php
// Generar token
$token = generarTokenCSRF();

// Verificar token
if (verificarTokenCSRF($_POST['csrf_token'])) {
    // Operación segura
}
```

#### Logging y Auditoría
```php
// Registrar eventos
registrarLog('LOGIN', 'Usuario inició sesión', $datos);
registrarLog('ERROR', 'Error de validación', $errores);
```

### Recomendaciones de Seguridad
1. **HTTPS**: Usar certificado SSL en producción
2. **Backup**: Respaldos regulares de la base de datos
3. **Actualizaciones**: Mantener PHP y MySQL actualizados
4. **Monitoreo**: Revisar logs de acceso regularmente

## 📊 Estadísticas y Reportes

### Tipos de Estadísticas
- **Distribución por Sexo**: Masculino vs Femenino
- **Distribución por Provincia**: Registros por ubicación
- **Distribución por Edad**: Rangos de edad
- **Estado Académico**: Estudiantes activos/inactivos
- **Discapacidad**: Con/sin discapacidad

### Formatos de Exportación
- **PDF**: Reporte completo con gráficas
- **Excel/CSV**: Datos estructurados para análisis
- **JSON**: API para integración con otros sistemas

### Gráficas Disponibles
- **Gráfica de Dona**: Distribución por sexo
- **Gráfica de Barras**: Estadísticas por provincia/edad
- **Gráfica Circular**: Estado académico
- **Gráfica de Líneas**: Resumen general

## 🧪 Testing

### Pruebas Recomendadas
```bash
# Validación de formularios
php -l *.php

# Pruebas de conexión
php -r "include 'conexion.php'; echo 'Conexión OK';"

# Verificación de permisos
ls -la imagenes/
```

### Casos de Prueba
1. **Registro de niño**: Datos válidos e inválidos
2. **Autenticación**: Login correcto e incorrecto
3. **Exportación**: Generación de reportes
4. **Validaciones**: Cédula, teléfono, fechas

## 🚀 Despliegue en Producción

### Configuración del Servidor
```apache
# .htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Seguridad
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

### Variables de Entorno
```bash
# .env
DB_HOST=localhost
DB_USER=sistema_user
DB_PASS=password_seguro
DB_NAME=sistema_ninos
APP_ENV=production
APP_DEBUG=false
```

### Optimizaciones
- **Caché**: Implementar caché de consultas frecuentes
- **CDN**: Usar CDN para recursos estáticos
- **Compresión**: Habilitar gzip en el servidor
- **Minificación**: Minificar CSS/JS

## 🤝 Contribución

### Cómo Contribuir
1. Fork del repositorio
2. Crear rama para nueva funcionalidad
3. Realizar cambios con commits descriptivos
4. Crear Pull Request con descripción detallada

### Estándares de Código
- **PSR-12**: Estándar de codificación PHP
- **Comentarios**: Documentación en español
- **Nombres**: Variables y funciones descriptivas
- **Estructura**: Separación de responsabilidades

### Reportar Issues
- Usar template de issue
- Incluir pasos para reproducir
- Especificar versión y entorno
- Adjuntar logs de error si aplica

## 📝 Changelog

### Versión 1.0.0 (2024)
- ✅ Sistema de registro completo
- ✅ Autenticación y sesiones
- ✅ Estadísticas con gráficas
- ✅ Exportación de reportes
- ✅ Interfaz responsive
- ✅ Validaciones de seguridad

### Próximas Versiones
- 🔄 API REST completa
- 🔄 Sistema de notificaciones
- 🔄 Backup automático
- 🔄 Integración con servicios externos

## 📞 Soporte

### Contacto
- **Email**: soporte@sistema-susana.com
- **Documentación**: [Wiki del proyecto](https://github.com/tu-usuario/sistema-susana/wiki)
- **Issues**: [GitHub Issues](https://github.com/tu-usuario/sistema-susana/issues)

### Recursos Adicionales
- **Documentación PHP**: [php.net](https://www.php.net/docs.php)
- **MySQL Reference**: [dev.mysql.com](https://dev.mysql.com/doc/)
- **Chart.js Docs**: [chartjs.org](https://www.chartjs.org/docs/)

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

```
MIT License

Copyright (c) 2024 Sistema Susana

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

**Desarrollado con ❤️ para el Sistema Susana**

*Última actualización: Diciembre 2024*
