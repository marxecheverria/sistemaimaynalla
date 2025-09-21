<?php
/**
 * Sistema de Registro de Niños - Página de Estadísticas
 * 
 * Esta página muestra estadísticas completas del sistema incluyendo:
 * - Distribución por sexo, provincia, edad
 * - Estado académico y discapacidad
 * - Gráficas interactivas usando Chart.js
 * - Exportación de datos en PDF y Excel
 * 
 * @author Sistema Susana
 * @version 1.0.0
 * @since 2024
 */

// Incluir archivos necesarios
require_once '../../config/routes.php';
include '../../verificar_sesion.php';
include '../../conexion.php';
include '../../includes/functions.php';

// Verificar que el usuario esté autenticado
requerirAutenticacion();

// Inicializar operaciones de base de datos
$operacionesDB = new OperacionesDB($conn);

// Obtener todas las estadísticas necesarias
try {
    // Estadísticas por sexo (Masculino/Femenino)
    $datos_sexo = $operacionesDB->obtenerEstadisticasSexo();
    
    // Estadísticas por provincia (ordenadas por cantidad)
    $datos_provincia = $operacionesDB->obtenerEstadisticasProvincia();
    
    // Estadísticas por rango de edad (0-5, 6-10, 11-15, 16-18, 18+)
    $datos_edad = $operacionesDB->obtenerEstadisticasEdad();
    
    // Estadísticas por estado académico (Si/No está estudiando)
    $datos_estado = $operacionesDB->obtenerEstadisticasEstadoAcademico();
    
    // Estadísticas por discapacidad (Si/No tiene discapacidad)
    $datos_discapacidad = $operacionesDB->obtenerEstadisticasDiscapacidad();
    
    // Total general de niños registrados
    $total_general = $operacionesDB->obtenerTotalNinos();
    
    // Registrar acceso a estadísticas
    registrarLog('ESTADISTICAS_ACCESS', 'Usuario accedió a la página de estadísticas');
    
} catch (Exception $e) {
    // Manejar errores de base de datos
    error_log("Error al obtener estadísticas: " . $e->getMessage());
    registrarLog('ERROR', 'Error al obtener estadísticas: ' . $e->getMessage());
    
    // Mostrar mensaje de error amigable
    $error_message = "Error al cargar las estadísticas. Por favor, intente más tarde.";
    $datos_sexo = $datos_provincia = $datos_edad = $datos_estado = $datos_discapacidad = [];
    $total_general = 0;
}

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Estadísticas del Sistema</title>
    <link rel="stylesheet" href="../../public/assets/css/menu.css">
    <link rel="stylesheet" href="../../public/assets/css/estilos-corporativos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 100px 20px 40px;
        }
        
        .stats-header {
            background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%);
            color: white;
            padding: 40px;
            border-radius: 20px 20px 0 0;
            text-align: center;
            margin-bottom: 0;
            position: relative;
            overflow: hidden;
        }
        
        .stats-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .stats-title {
            font-size: 2.5em;
            font-weight: 700;
            margin: 0 0 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }
        
        .stats-subtitle {
            font-size: 1.2em;
            opacity: 0.9;
            font-weight: 300;
            position: relative;
            z-index: 1;
        }
        
        .stats-container {
            background: #fff;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            border-left: 5px solid #dda619;
            box-shadow: 0 4px 12px rgba(28,44,80,0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            color: #1c2c50;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1em;
            color: #6c757d;
            font-weight: 500;
        }
        
        .charts-section {
            margin-top: 40px;
        }
        
        .section-title {
            font-size: 1.8em;
            font-weight: 600;
            color: #1c2c50;
            margin-bottom: 30px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .section-title::before,
        .section-title::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, transparent, #dda619, transparent);
            margin: 0 20px;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .chart-container {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        
        .chart-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #1c2c50;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .chart-wrapper {
            position: relative;
            height: 300px;
        }
        
        .export-buttons {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
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
        
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(40,167,69,0.3);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #dda619 0%, #c49a0f 100%);
            color: white;
        }
        
        .btn-warning:hover {
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
            
            .stats-header {
                padding: 30px 20px;
            }
            
            .stats-title {
                font-size: 2em;
            }
            
            .stats-container {
                padding: 30px 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .btn {
                display: block;
                margin: 10px 0;
                width: 100%;
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
                    <a href="../../estadisticas" class="nav-link active">
                        <span class="icon">📊</span> Estadísticas
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
        <!-- Header de estadísticas -->
        <div class="stats-header">
            <h1 class="stats-title">📊 Estadísticas del Sistema</h1>
            <p class="stats-subtitle">Análisis completo de datos registrados</p>
        </div>
        
        <!-- Contenedor principal -->
        <div class="stats-container">
            <!-- Tarjetas de resumen -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?= $total_general ?></div>
                    <div class="stat-label">Total de Niños Registrados</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👦</div>
                    <div class="stat-number"><?= $datos_sexo['Masculino'] ?? 0 ?></div>
                    <div class="stat-label">Niños Masculinos</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👧</div>
                    <div class="stat-number"><?= $datos_sexo['Femenino'] ?? 0 ?></div>
                    <div class="stat-label">Niñas Femeninas</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-number"><?= $datos_estado['Si'] ?? 0 ?></div>
                    <div class="stat-label">Estudiantes Activos</div>
                </div>
            </div>
            
            <!-- Sección de gráficas -->
            <div class="charts-section">
                <h2 class="section-title">📈 Gráficas de Análisis</h2>
                
                <div class="charts-grid">
                    <!-- Gráfica por Sexo -->
                    <div class="chart-container">
                        <h3 class="chart-title">Distribución por Sexo</h3>
                        <div class="chart-wrapper">
                            <canvas id="sexoChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Gráfica por Provincia -->
                    <div class="chart-container">
                        <h3 class="chart-title">Distribución por Provincia</h3>
                        <div class="chart-wrapper">
                            <canvas id="provinciaChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Gráfica por Edad -->
                    <div class="chart-container">
                        <h3 class="chart-title">Distribución por Rango de Edad</h3>
                        <div class="chart-wrapper">
                            <canvas id="edadChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Gráfica por Estado Académico -->
                    <div class="chart-container">
                        <h3 class="chart-title">Estado Académico</h3>
                        <div class="chart-wrapper">
                            <canvas id="estadoChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Gráfica por Discapacidad -->
                    <div class="chart-container">
                        <h3 class="chart-title">Distribución por Discapacidad</h3>
                        <div class="chart-wrapper">
                            <canvas id="discapacidadChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Gráfica Combinada -->
                    <div class="chart-container">
                        <h3 class="chart-title">Resumen General</h3>
                        <div class="chart-wrapper">
                            <canvas id="resumenChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botones de exportación -->
            <div class="export-buttons">
                <button class="btn btn-primary" onclick="exportarPDF()">
                    <span style="margin-right: 8px;">📄</span>
                    Exportar PDF
                </button>
                <button class="btn btn-success" onclick="exportarExcel()">
                    <span style="margin-right: 8px;">📊</span>
                    Exportar Excel
                </button>
                <a href="../../panel" class="btn btn-warning">
                    <span style="margin-right: 8px;">⬅️</span>
                    Volver al Panel
                </a>
            </div>
        </div>
    </div>
    
    <!-- Ayuda flotante -->
    <div class="floating-help" onclick="showHelp()">
        <span style="margin-right: 8px;">❓</span>
        ¿Necesitas ayuda?
    </div>

    <script>
        // Datos para las gráficas
        const datosSexo = <?= json_encode($datos_sexo) ?>;
        const datosProvincia = <?= json_encode($datos_provincia) ?>;
        const datosEdad = <?= json_encode($datos_edad) ?>;
        const datosEstado = <?= json_encode($datos_estado) ?>;
        const datosDiscapacidad = <?= json_encode($datos_discapacidad) ?>;
        
        // Configuración común para las gráficas
        Chart.defaults.font.family = 'Montserrat, sans-serif';
        Chart.defaults.font.size = 12;
        
        // Colores corporativos
        const colores = {
            primario: '#1c2c50',
            secundario: '#dda619',
            success: '#28a745',
            info: '#17a2b8',
            warning: '#ffc107',
            danger: '#dc3545',
            light: '#f8f9fa',
            dark: '#343a40'
        };
        
        // Gráfica por Sexo
        const ctxSexo = document.getElementById('sexoChart').getContext('2d');
        new Chart(ctxSexo, {
            type: 'doughnut',
            data: {
                labels: Object.keys(datosSexo),
                datasets: [{
                    data: Object.values(datosSexo),
                    backgroundColor: [colores.primario, colores.secondario],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
        
        // Gráfica por Provincia
        const ctxProvincia = document.getElementById('provinciaChart').getContext('2d');
        new Chart(ctxProvincia, {
            type: 'bar',
            data: {
                labels: Object.keys(datosProvincia),
                datasets: [{
                    label: 'Niños por Provincia',
                    data: Object.values(datosProvincia),
                    backgroundColor: colores.primario,
                    borderColor: colores.secondario,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        
        // Gráfica por Edad
        const ctxEdad = document.getElementById('edadChart').getContext('2d');
        new Chart(ctxEdad, {
            type: 'bar',
            data: {
                labels: Object.keys(datosEdad),
                datasets: [{
                    label: 'Niños por Edad',
                    data: Object.values(datosEdad),
                    backgroundColor: colores.success,
                    borderColor: colores.primario,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        
        // Gráfica por Estado Académico
        const ctxEstado = document.getElementById('estadoChart').getContext('2d');
        new Chart(ctxEstado, {
            type: 'pie',
            data: {
                labels: Object.keys(datosEstado).map(key => key === 'Si' ? 'Estudiando' : 'No Estudiando'),
                datasets: [{
                    data: Object.values(datosEstado),
                    backgroundColor: [colores.success, colores.danger],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
        
        // Gráfica por Discapacidad
        const ctxDiscapacidad = document.getElementById('discapacidadChart').getContext('2d');
        new Chart(ctxDiscapacidad, {
            type: 'doughnut',
            data: {
                labels: Object.keys(datosDiscapacidad).map(key => key === 'Si' ? 'Con Discapacidad' : 'Sin Discapacidad'),
                datasets: [{
                    data: Object.values(datosDiscapacidad),
                    backgroundColor: [colores.warning, colores.info],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
        
        // Gráfica de Resumen
        const ctxResumen = document.getElementById('resumenChart').getContext('2d');
        new Chart(ctxResumen, {
            type: 'line',
            data: {
                labels: ['Total', 'Masculino', 'Femenino', 'Estudiantes', 'Con Discapacidad'],
                datasets: [{
                    label: 'Resumen General',
                    data: [
                        <?= $total_general ?>,
                        <?= $datos_sexo['Masculino'] ?? 0 ?>,
                        <?= $datos_sexo['Femenino'] ?? 0 ?>,
                        <?= $datos_estado['Si'] ?? 0 ?>,
                        <?= $datos_discapacidad['Si'] ?? 0 ?>
                    ],
                    borderColor: colores.primario,
                    backgroundColor: colores.secondario + '20',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        
        // Funciones de exportación
        function exportarPDF() {
            window.print();
        }
        
        function exportarExcel() {
            // Crear datos para Excel
            const datos = {
                'Resumen General': [
                    ['Métrica', 'Valor'],
                    ['Total de Niños', <?= $total_general ?>],
                    ['Masculinos', <?= $datos_sexo['Masculino'] ?? 0 ?>],
                    ['Femeninos', <?= $datos_sexo['Femenino'] ?? 0 ?>],
                    ['Estudiantes Activos', <?= $datos_estado['Si'] ?? 0 ?>],
                    ['Con Discapacidad', <?= $datos_discapacidad['Si'] ?? 0 ?>]
                ],
                'Por Provincia': [
                    ['Provincia', 'Total'],
                    ...Object.entries(datosProvincia).map(([provincia, total]) => [provincia, total])
                ],
                'Por Edad': [
                    ['Rango de Edad', 'Total'],
                    ...Object.entries(datosEdad).map(([edad, total]) => [edad, total])
                ]
            };
            
            // Crear archivo CSV
            let csvContent = '';
            Object.entries(datos).forEach(([hoja, filas]) => {
                csvContent += hoja + '\n';
                filas.forEach(fila => {
                    csvContent += fila.join(',') + '\n';
                });
                csvContent += '\n';
            });
            
            // Descargar archivo
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'estadisticas_sistema.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Función de ayuda
        function showHelp() {
            alert('Ayuda para Estadísticas:\n\n' +
                  '• Las gráficas muestran datos en tiempo real\n' +
                  '• Puede exportar los datos en PDF o Excel\n' +
                  '• Los datos se actualizan automáticamente\n' +
                  '• Use los botones de exportación para generar reportes\n' +
                  '• Las estadísticas incluyen análisis por sexo, provincia y edad');
        }
    </script>
</body>
</html>

