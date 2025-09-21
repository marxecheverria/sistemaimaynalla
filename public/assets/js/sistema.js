/**
 * Sistema de Registro de Niños - JavaScript Principal
 * 
 * Este archivo contiene funciones JavaScript comunes para todo el sistema,
 * incluyendo validaciones, utilidades y funciones de interfaz.
 * 
 * @author Sistema Susana
 * @version 1.0.0
 * @since 2024
 */

// Configuración global
const SistemaConfig = {
    version: '1.0.0',
    debug: false,
    apiUrl: '/api/',
    timeout: 30000
};

/**
 * Clase para manejo de validaciones del lado del cliente
 */
class ValidadorCliente {
    
    /**
     * Valida formato de cédula ecuatoriana
     * @param {string} cedula - Cédula a validar
     * @returns {boolean} - True si es válida
     */
    static validarCedula(cedula) {
        // Remover espacios y caracteres no numéricos
        cedula = cedula.replace(/\D/g, '');
        
        // Verificar longitud
        if (cedula.length !== 10) {
            return false;
        }
        
        // Verificar que no sean todos los dígitos iguales
        if (/^(\d)\1{9}$/.test(cedula)) {
            return false;
        }
        
        // Algoritmo de validación de cédula ecuatoriana
        const coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        let suma = 0;
        
        for (let i = 0; i < 9; i++) {
            let producto = parseInt(cedula[i]) * coeficientes[i];
            suma += (producto > 9) ? producto - 9 : producto;
        }
        
        const digitoVerificador = (10 - (suma % 10)) % 10;
        
        return digitoVerificador == cedula[9];
    }
    
    /**
     * Valida formato de teléfono ecuatoriano
     * @param {string} telefono - Teléfono a validar
     * @returns {boolean} - True si es válido
     */
    static validarTelefono(telefono) {
        // Remover espacios y caracteres no numéricos
        telefono = telefono.replace(/\D/g, '');
        
        // Verificar longitud (7-10 dígitos)
        return telefono.length >= 7 && telefono.length <= 10;
    }
    
    /**
     * Valida formato de email
     * @param {string} email - Email a validar
     * @returns {boolean} - True si es válido
     */
    static validarEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    /**
     * Valida que un campo no esté vacío
     * @param {string} valor - Valor a validar
     * @returns {boolean} - True si no está vacío
     */
    static validarRequerido(valor) {
        return valor && valor.trim().length > 0;
    }
}

/**
 * Clase para manejo de formularios
 */
class ManejadorFormularios {
    
    /**
     * Inicializa validación en tiempo real para un formulario
     * @param {string} formId - ID del formulario
     */
    static inicializarValidacion(formId) {
        const formulario = document.getElementById(formId);
        if (!formulario) return;
        
        // Validar campos de cédula
        const camposCedula = formulario.querySelectorAll('input[pattern*="10"]');
        camposCedula.forEach(campo => {
            campo.addEventListener('blur', function() {
                this.classList.remove('error', 'success');
                if (this.value) {
                    if (ValidadorCliente.validarCedula(this.value)) {
                        this.classList.add('success');
                        this.classList.remove('error');
                    } else {
                        this.classList.add('error');
                        this.classList.remove('success');
                    }
                }
            });
        });
        
        // Validar campos de teléfono
        const camposTelefono = formulario.querySelectorAll('input[type="tel"]');
        camposTelefono.forEach(campo => {
            campo.addEventListener('blur', function() {
                this.classList.remove('error', 'success');
                if (this.value) {
                    if (ValidadorCliente.validarTelefono(this.value)) {
                        this.classList.add('success');
                        this.classList.remove('error');
                    } else {
                        this.classList.add('error');
                        this.classList.remove('success');
                    }
                }
            });
        });
        
        // Validar campos requeridos
        const camposRequeridos = formulario.querySelectorAll('input[required], select[required], textarea[required]');
        camposRequeridos.forEach(campo => {
            campo.addEventListener('blur', function() {
                this.classList.remove('error', 'success');
                if (ValidadorCliente.validarRequerido(this.value)) {
                    this.classList.add('success');
                    this.classList.remove('error');
                } else {
                    this.classList.add('error');
                    this.classList.remove('success');
                }
            });
        });
    }
    
    /**
     * Valida todo el formulario antes de enviar
     * @param {string} formId - ID del formulario
     * @returns {boolean} - True si es válido
     */
    static validarFormulario(formId) {
        const formulario = document.getElementById(formId);
        if (!formulario) return false;
        
        let esValido = true;
        
        // Validar campos requeridos
        const camposRequeridos = formulario.querySelectorAll('input[required], select[required], textarea[required]');
        camposRequeridos.forEach(campo => {
            if (!ValidadorCliente.validarRequerido(campo.value)) {
                campo.classList.add('error');
                esValido = false;
            }
        });
        
        // Validar cédulas
        const camposCedula = formulario.querySelectorAll('input[pattern*="10"]');
        camposCedula.forEach(campo => {
            if (campo.value && !ValidadorCliente.validarCedula(campo.value)) {
                campo.classList.add('error');
                esValido = false;
            }
        });
        
        // Validar teléfonos
        const camposTelefono = formulario.querySelectorAll('input[type="tel"]');
        camposTelefono.forEach(campo => {
            if (campo.value && !ValidadorCliente.validarTelefono(campo.value)) {
                campo.classList.add('error');
                esValido = false;
            }
        });
        
        return esValido;
    }
}

/**
 * Clase para manejo de notificaciones
 */
class Notificaciones {
    
    /**
     * Muestra una notificación de éxito
     * @param {string} mensaje - Mensaje a mostrar
     * @param {number} duracion - Duración en milisegundos
     */
    static exito(mensaje, duracion = 3000) {
        this.mostrar(mensaje, 'success', duracion);
    }
    
    /**
     * Muestra una notificación de error
     * @param {string} mensaje - Mensaje a mostrar
     * @param {number} duracion - Duración en milisegundos
     */
    static error(mensaje, duracion = 5000) {
        this.mostrar(mensaje, 'error', duracion);
    }
    
    /**
     * Muestra una notificación de información
     * @param {string} mensaje - Mensaje a mostrar
     * @param {number} duracion - Duración en milisegundos
     */
    static info(mensaje, duracion = 3000) {
        this.mostrar(mensaje, 'info', duracion);
    }
    
    /**
     * Muestra una notificación
     * @param {string} mensaje - Mensaje a mostrar
     * @param {string} tipo - Tipo de notificación
     * @param {number} duracion - Duración en milisegundos
     */
    static mostrar(mensaje, tipo = 'info', duracion = 3000) {
        // Crear contenedor si no existe
        let contenedor = document.getElementById('notificaciones');
        if (!contenedor) {
            contenedor = document.createElement('div');
            contenedor.id = 'notificaciones';
            contenedor.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                max-width: 400px;
            `;
            document.body.appendChild(contenedor);
        }
        
        // Crear notificación
        const notificacion = document.createElement('div');
        notificacion.style.cssText = `
            background: ${this.getColorFondo(tipo)};
            color: ${this.getColorTexto(tipo)};
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-left: 4px solid ${this.getColorBorde(tipo)};
            animation: slideIn 0.3s ease-out;
        `;
        
        notificacion.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 18px;">${this.getIcono(tipo)}</span>
                <span>${mensaje}</span>
                <button onclick="this.parentElement.parentElement.remove()" style="
                    background: none;
                    border: none;
                    color: inherit;
                    font-size: 18px;
                    cursor: pointer;
                    margin-left: auto;
                ">&times;</button>
            </div>
        `;
        
        contenedor.appendChild(notificacion);
        
        // Auto-remover después de la duración
        setTimeout(() => {
            if (notificacion.parentNode) {
                notificacion.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => {
                    if (notificacion.parentNode) {
                        notificacion.remove();
                    }
                }, 300);
            }
        }, duracion);
    }
    
    /**
     * Obtiene el color de fondo según el tipo
     * @param {string} tipo - Tipo de notificación
     * @returns {string} - Color de fondo
     */
    static getColorFondo(tipo) {
        const colores = {
            success: '#d4edda',
            error: '#f8d7da',
            info: '#d1ecf1',
            warning: '#fff3cd'
        };
        return colores[tipo] || colores.info;
    }
    
    /**
     * Obtiene el color de texto según el tipo
     * @param {string} tipo - Tipo de notificación
     * @returns {string} - Color de texto
     */
    static getColorTexto(tipo) {
        const colores = {
            success: '#155724',
            error: '#721c24',
            info: '#0c5460',
            warning: '#856404'
        };
        return colores[tipo] || colores.info;
    }
    
    /**
     * Obtiene el color de borde según el tipo
     * @param {string} tipo - Tipo de notificación
     * @returns {string} - Color de borde
     */
    static getColorBorde(tipo) {
        const colores = {
            success: '#28a745',
            error: '#dc3545',
            info: '#17a2b8',
            warning: '#ffc107'
        };
        return colores[tipo] || colores.info;
    }
    
    /**
     * Obtiene el icono según el tipo
     * @param {string} tipo - Tipo de notificación
     * @returns {string} - Icono
     */
    static getIcono(tipo) {
        const iconos = {
            success: '✅',
            error: '❌',
            info: 'ℹ️',
            warning: '⚠️'
        };
        return iconos[tipo] || iconos.info;
    }
}

/**
 * Clase para manejo de AJAX
 */
class AjaxHandler {
    
    /**
     * Realiza una petición AJAX
     * @param {string} url - URL de la petición
     * @param {Object} options - Opciones de la petición
     * @returns {Promise} - Promise con la respuesta
     */
    static async request(url, options = {}) {
        const config = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            timeout: SistemaConfig.timeout,
            ...options
        };
        
        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return data;
            
        } catch (error) {
            console.error('Error en petición AJAX:', error);
            Notificaciones.error('Error en la comunicación con el servidor');
            throw error;
        }
    }
    
    /**
     * Realiza una petición POST
     * @param {string} url - URL de la petición
     * @param {Object} data - Datos a enviar
     * @returns {Promise} - Promise con la respuesta
     */
    static async post(url, data) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
    
    /**
     * Realiza una petición GET
     * @param {string} url - URL de la petición
     * @returns {Promise} - Promise con la respuesta
     */
    static async get(url) {
        return this.request(url);
    }
}

/**
 * Clase para utilidades generales
 */
class Utilidades {
    
    /**
     * Formatea una fecha para mostrar
     * @param {string|Date} fecha - Fecha a formatear
     * @param {string} formato - Formato de salida
     * @returns {string} - Fecha formateada
     */
    static formatearFecha(fecha, formato = 'dd/mm/yyyy') {
        if (!fecha) return 'N/A';
        
        const fechaObj = new Date(fecha);
        if (isNaN(fechaObj.getTime())) return 'N/A';
        
        const dia = fechaObj.getDate().toString().padStart(2, '0');
        const mes = (fechaObj.getMonth() + 1).toString().padStart(2, '0');
        const año = fechaObj.getFullYear();
        
        return formato
            .replace('dd', dia)
            .replace('mm', mes)
            .replace('yyyy', año);
    }
    
    /**
     * Formatea un número de teléfono
     * @param {string} telefono - Teléfono a formatear
     * @returns {string} - Teléfono formateado
     */
    static formatearTelefono(telefono) {
        if (!telefono) return 'N/A';
        
        // Remover caracteres no numéricos
        const numeros = telefono.replace(/\D/g, '');
        
        // Formatear según longitud
        if (numeros.length === 10) {
            return `${numeros.slice(0, 3)}-${numeros.slice(3, 6)}-${numeros.slice(6)}`;
        } else if (numeros.length === 9) {
            return `${numeros.slice(0, 2)}-${numeros.slice(2, 5)}-${numeros.slice(5)}`;
        }
        
        return telefono;
    }
    
    /**
     * Formatea una cédula
     * @param {string} cedula - Cédula a formatear
     * @returns {string} - Cédula formateada
     */
    static formatearCedula(cedula) {
        if (!cedula) return 'N/A';
        
        // Remover caracteres no numéricos
        const numeros = cedula.replace(/\D/g, '');
        
        // Formatear cédula ecuatoriana
        if (numeros.length === 10) {
            return `${numeros.slice(0, 2)}.${numeros.slice(2, 8)}.${numeros.slice(8)}`;
        }
        
        return cedula;
    }
    
    /**
     * Calcula la edad basada en la fecha de nacimiento
     * @param {string|Date} fechaNacimiento - Fecha de nacimiento
     * @returns {number} - Edad en años
     */
    static calcularEdad(fechaNacimiento) {
        if (!fechaNacimiento) return 0;
        
        const fechaNac = new Date(fechaNacimiento);
        const fechaActual = new Date();
        
        if (isNaN(fechaNac.getTime())) return 0;
        
        let edad = fechaActual.getFullYear() - fechaNac.getFullYear();
        const mesActual = fechaActual.getMonth();
        const mesNac = fechaNac.getMonth();
        
        if (mesActual < mesNac || (mesActual === mesNac && fechaActual.getDate() < fechaNac.getDate())) {
            edad--;
        }
        
        return edad;
    }
    
    /**
     * Debounce para limitar la frecuencia de ejecución de funciones
     * @param {Function} func - Función a ejecutar
     * @param {number} wait - Tiempo de espera en milisegundos
     * @returns {Function} - Función con debounce
     */
    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    /**
     * Copia texto al portapapeles
     * @param {string} texto - Texto a copiar
     * @returns {Promise<boolean>} - True si se copió exitosamente
     */
    static async copiarPortapapeles(texto) {
        try {
            await navigator.clipboard.writeText(texto);
            Notificaciones.exito('Texto copiado al portapapeles');
            return true;
        } catch (error) {
            console.error('Error al copiar:', error);
            Notificaciones.error('No se pudo copiar el texto');
            return false;
        }
    }
}

/**
 * Clase para manejo de gráficas
 */
class ManejadorGraficas {
    
    /**
     * Configuración común para Chart.js
     */
    static configuracionComun = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true,
                    font: {
                        family: 'Montserrat, sans-serif',
                        size: 12
                    }
                }
            }
        }
    };
    
    /**
     * Colores corporativos para las gráficas
     */
    static colores = {
        primario: '#1c2c50',
        secundario: '#dda619',
        success: '#28a745',
        info: '#17a2b8',
        warning: '#ffc107',
        danger: '#dc3545',
        light: '#f8f9fa',
        dark: '#343a40'
    };
    
    /**
     * Crea una gráfica de dona
     * @param {string} canvasId - ID del canvas
     * @param {Object} datos - Datos de la gráfica
     * @param {Object} opciones - Opciones adicionales
     */
    static crearDona(canvasId, datos, opciones = {}) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        
        const config = {
            type: 'doughnut',
            data: {
                labels: datos.labels,
                datasets: [{
                    data: datos.values,
                    backgroundColor: datos.colores || Object.values(this.colores),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                ...this.configuracionComun,
                ...opciones
            }
        };
        
        new Chart(ctx, config);
    }
    
    /**
     * Crea una gráfica de barras
     * @param {string} canvasId - ID del canvas
     * @param {Object} datos - Datos de la gráfica
     * @param {Object} opciones - Opciones adicionales
     */
    static crearBarras(canvasId, datos, opciones = {}) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        
        const config = {
            type: 'bar',
            data: {
                labels: datos.labels,
                datasets: [{
                    label: datos.label || 'Datos',
                    data: datos.values,
                    backgroundColor: datos.color || this.colores.primario,
                    borderColor: datos.borderColor || this.colores.secondario,
                    borderWidth: 2
                }]
            },
            options: {
                ...this.configuracionComun,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                ...opciones
            }
        };
        
        new Chart(ctx, config);
    }
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Configurar Chart.js con fuente corporativa
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = 'Montserrat, sans-serif';
        Chart.defaults.font.size = 12;
    }
    
    // Inicializar validación en todos los formularios
    const formularios = document.querySelectorAll('form[id]');
    formularios.forEach(formulario => {
        ManejadorFormularios.inicializarValidacion(formulario.id);
    });
    
    // Agregar estilos CSS para animaciones de notificaciones
    if (!document.getElementById('notificaciones-styles')) {
        const style = document.createElement('style');
        style.id = 'notificaciones-styles';
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
});

// Exportar clases para uso global
window.ValidadorCliente = ValidadorCliente;
window.ManejadorFormularios = ManejadorFormularios;
window.Notificaciones = Notificaciones;
window.AjaxHandler = AjaxHandler;
window.Utilidades = Utilidades;
window.ManejadorGraficas = ManejadorGraficas;
window.SistemaConfig = SistemaConfig;
