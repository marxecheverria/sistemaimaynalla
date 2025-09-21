<?php
include '../../verificar_sesion.php';
include '../../conexion.php';

// Verificar si se solicita exportación
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    // Configurar headers para PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="reporte_estadisticas_' . date('Y-m-d') . '.pdf"');
    
    // Crear contenido HTML para PDF
    $html = generarContenidoPDF();
    
    // Usar una librería simple de PDF o generar HTML que se pueda convertir
    echo $html;
    exit;
}

function generarContenidoPDF() {
    global $conn;
    
    // Obtener todas las estadísticas
    $sql_sexo = "SELECT sexo, COUNT(*) as total FROM ninos GROUP BY sexo";
    $result_sexo = $conn->query($sql_sexo);
    $datos_sexo = [];
    while ($row = $result_sexo->fetch_assoc()) {
        $datos_sexo[$row['sexo']] = $row['total'];
    }
    
    $sql_provincia = "SELECT provincia, COUNT(*) as total FROM ninos GROUP BY provincia ORDER BY total DESC";
    $result_provincia = $conn->query($sql_provincia);
    $datos_provincia = [];
    while ($row = $result_provincia->fetch_assoc()) {
        $datos_provincia[$row['provincia']] = $row['total'];
    }
    
    $sql_edad = "SELECT 
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
    $result_edad = $conn->query($sql_edad);
    $datos_edad = [];
    while ($row = $result_edad->fetch_assoc()) {
        $datos_edad[$row['rango_edad']] = $row['total'];
    }
    
    $sql_total = "SELECT COUNT(*) as total FROM ninos";
    $result_total = $conn->query($sql_total);
    $total_general = $result_total->fetch_assoc()['total'];
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte de Estadísticas</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .title { font-size: 24px; font-weight: bold; color: #1c2c50; }
            .subtitle { font-size: 14px; color: #666; margin-top: 5px; }
            .section { margin-bottom: 25px; }
            .section-title { font-size: 18px; font-weight: bold; color: #1c2c50; margin-bottom: 15px; border-bottom: 2px solid #dda619; padding-bottom: 5px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #1c2c50; color: white; }
            .summary { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            .summary-item { display: inline-block; margin-right: 30px; }
            .summary-number { font-size: 20px; font-weight: bold; color: #1c2c50; }
            .summary-label { font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="title">📊 REPORTE DE ESTADÍSTICAS DEL SISTEMA</div>
            <div class="subtitle">Generado el ' . date('d/m/Y H:i:s') . '</div>
        </div>
        
        <div class="summary">
            <div class="summary-item">
                <div class="summary-number">' . $total_general . '</div>
                <div class="summary-label">Total de Niños</div>
            </div>
            <div class="summary-item">
                <div class="summary-number">' . ($datos_sexo['Masculino'] ?? 0) . '</div>
                <div class="summary-label">Masculinos</div>
            </div>
            <div class="summary-item">
                <div class="summary-number">' . ($datos_sexo['Femenino'] ?? 0) . '</div>
                <div class="summary-label">Femeninos</div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Distribución por Sexo</div>
            <table>
                <tr><th>Sexo</th><th>Cantidad</th><th>Porcentaje</th></tr>';
    
    foreach ($datos_sexo as $sexo => $total) {
        $porcentaje = $total_general > 0 ? round(($total / $total_general) * 100, 2) : 0;
        $html .= '<tr><td>' . $sexo . '</td><td>' . $total . '</td><td>' . $porcentaje . '%</td></tr>';
    }
    
    $html .= '
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">Distribución por Provincia</div>
            <table>
                <tr><th>Provincia</th><th>Cantidad</th><th>Porcentaje</th></tr>';
    
    foreach ($datos_provincia as $provincia => $total) {
        $porcentaje = $total_general > 0 ? round(($total / $total_general) * 100, 2) : 0;
        $html .= '<tr><td>' . $provincia . '</td><td>' . $total . '</td><td>' . $porcentaje . '%</td></tr>';
    }
    
    $html .= '
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">Distribución por Rango de Edad</div>
            <table>
                <tr><th>Rango de Edad</th><th>Cantidad</th><th>Porcentaje</th></tr>';
    
    foreach ($datos_edad as $edad => $total) {
        $porcentaje = $total_general > 0 ? round(($total / $total_general) * 100, 2) : 0;
        $html .= '<tr><td>' . $edad . '</td><td>' . $total . '</td><td>' . $porcentaje . '%</td></tr>';
    }
    
    $html .= '
            </table>
        </div>
        
        <div style="margin-top: 40px; text-align: center; font-size: 12px; color: #666;">
            <p>Este reporte fue generado automáticamente por el Sistema de Registro de Niños</p>
            <p>Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

// Si no es exportación, mostrar la página normal
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📄 Generar Reportes</title>
    <link rel="stylesheet" href="../../public/assets/css/menu.css">
    <link rel="stylesheet" href="../../public/assets/css/estilos-corporativos.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .main-content {
            max-width: 1000px;
            margin: 0 auto;
            padding: 100px 20px 40px;
        }
        
        .report-container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .report-header {
            background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .report-title {
            font-size: 2.5em;
            font-weight: 700;
            margin: 0 0 10px;
        }
        
        .report-subtitle {
            font-size: 1.2em;
            opacity: 0.9;
        }
        
        .report-body {
            padding: 40px;
        }
        
        .report-section {
            margin-bottom: 40px;
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            border-left: 5px solid #dda619;
        }
        
        .section-title {
            font-size: 1.5em;
            font-weight: 600;
            color: #1c2c50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .section-icon {
            margin-right: 15px;
            font-size: 1.2em;
        }
        
        .report-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .report-option {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .report-option:hover {
            border-color: #dda619;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(221,166,25,0.1);
        }
        
        .option-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }
        
        .option-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #1c2c50;
            margin-bottom: 10px;
        }
        
        .option-description {
            color: #6c757d;
            font-size: 0.9em;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1em;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1c2c50 0%, #2a4a7a 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(28,44,80,0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40,167,69,0.3);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #dda619 0%, #c49a0f 100%);
            color: white;
        }
        
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(221,166,25,0.3);
        }
        
        .back-button {
            text-align: center;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 100px 10px 20px;
            }
            
            .report-header {
                padding: 30px 20px;
            }
            
            .report-title {
                font-size: 2em;
            }
            
            .report-body {
                padding: 30px 20px;
            }
            
            .report-options {
                grid-template-columns: 1fr;
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
                    <a href="../../estadisticas" class="nav-link">
                        <span class="icon">📊</span> Estadísticas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../../reportes" class="nav-link active">
                        <span class="icon">📄</span> Reportes
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
        <div class="report-container">
            <!-- Header del reporte -->
            <div class="report-header">
                <h1 class="report-title">📄 Generar Reportes</h1>
                <p class="report-subtitle">Exporta estadísticas y datos del sistema</p>
            </div>
            
            <!-- Cuerpo del reporte -->
            <div class="report-body">
                <!-- Sección de opciones de reporte -->
                <div class="report-section">
                    <h2 class="section-title">
                        <span class="section-icon">📊</span>
                        Tipos de Reportes Disponibles
                    </h2>
                    
                    <div class="report-options">
                        <!-- Reporte PDF -->
                        <div class="report-option" onclick="generarReporte('pdf')">
                            <div class="option-icon">📄</div>
                            <div class="option-title">Reporte PDF</div>
                            <div class="option-description">
                                Genera un reporte completo en formato PDF con todas las estadísticas del sistema
                            </div>
                            <button class="btn btn-primary">Generar PDF</button>
                        </div>
                        
                        <!-- Reporte Excel -->
                        <div class="report-option" onclick="generarReporte('excel')">
                            <div class="option-icon">📊</div>
                            <div class="option-title">Reporte Excel</div>
                            <div class="option-description">
                                Exporta los datos en formato CSV/Excel para análisis detallado
                            </div>
                            <button class="btn btn-success">Generar Excel</button>
                        </div>
                        
                        <!-- Reporte Detallado -->
                        <div class="report-option" onclick="generarReporte('detallado')">
                            <div class="option-icon">📋</div>
                            <div class="option-title">Reporte Detallado</div>
                            <div class="option-description">
                                Lista completa de todos los niños registrados con información detallada
                            </div>
                            <button class="btn btn-warning">Ver Detallado</button>
                        </div>
                    </div>
                </div>
                
                <!-- Información adicional -->
                <div class="report-section">
                    <h2 class="section-title">
                        <span class="section-icon">ℹ️</span>
                        Información sobre los Reportes
                    </h2>
                    
                    <div style="color: #6c757d; line-height: 1.6;">
                        <p><strong>📄 Reporte PDF:</strong> Incluye gráficas, estadísticas por sexo, provincia, edad y resumen ejecutivo.</p>
                        <p><strong>📊 Reporte Excel:</strong> Datos estructurados en hojas de cálculo para análisis avanzado.</p>
                        <p><strong>📋 Reporte Detallado:</strong> Lista completa con todos los datos de cada niño registrado.</p>
                        <p><strong>🔄 Actualización:</strong> Todos los reportes se generan con datos en tiempo real.</p>
                    </div>
                </div>
                
                <!-- Botón de regreso -->
                <div class="back-button">
                    <a href="../../estadisticas" class="btn btn-primary">
                        <span style="margin-right: 8px;">⬅️</span>
                        Volver a Estadísticas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function generarReporte(tipo) {
            switch(tipo) {
                case 'pdf':
                    window.open('reportes.php?export=pdf', '_blank');
                    break;
                case 'excel':
                    // Redirigir a estadísticas para usar la función de Excel existente
                    window.location.href = 'estadisticas.php';
                    setTimeout(() => {
                        if (typeof exportarExcel === 'function') {
                            exportarExcel();
                        }
                    }, 1000);
                    break;
                case 'detallado':
                    window.open('ver.php', '_blank');
                    break;
            }
        }
    </script>
</body>
</html>

