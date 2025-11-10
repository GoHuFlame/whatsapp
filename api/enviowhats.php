<?php

$configuracion = [
    'api_url' => getenv('MERCATELY_API_URL'),
    'api_key' => getenv('MERCATELY_API_KEY'),
    'internal_id' => getenv('MERCATELY_INTERNAL_ID'),
];

// ============================================================================
// SECCIÓN 2: PROCESAMIENTO DEL FORMULARIO
// ============================================================================

$mensajeError = '';
$esExitoso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($configuracion['api_url']) || empty($configuracion['api_key']) || empty($configuracion['internal_id'])) {
            throw new Exception('Error de configuración: Faltan variables de entorno');
        }
        
        if (!isset($_POST['phone_number']) || empty(trim($_POST['phone_number']))) {
            throw new Exception('El número de teléfono es requerido');
        }
        
        $telefono = preg_replace('/\D/', '', $_POST['phone_number']);
        if (strlen($telefono) !== 10) {
            throw new Exception('El número debe tener exactamente 10 dígitos');
        }
        
        $telefonoCompleto = '521' . $telefono;
        
        $parametrosPlantilla = [];
        
        $camposRequeridos = [
            'patient_name' => 'El nombre del paciente es requerido',
            'doctor_name' => 'El nombre del doctor es requerido',
            'appointment_date' => 'La fecha de la cita es requerida',
            'appointment_time' => 'La hora de la cita es requerida',
            'consultory_number' => 'El número de consultorio es requerido'
        ];
        
        foreach ($camposRequeridos as $campo => $mensaje) {
            if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
                throw new Exception($mensaje);
            }
            $parametrosPlantilla[] = trim($_POST[$campo]);
        }
        
        $datos = [
            "phone_number" => $telefonoCompleto,
            "internal_id" => $configuracion['internal_id'],
            "template_params" => $parametrosPlantilla
        ];
        
        $ch = curl_init($configuracion['api_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'api-key: ' . $configuracion['api_key'],
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        
        $respuesta = curl_exec($ch);
        $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorCurl = curl_error($ch);
        curl_close($ch);

        if ($codigoHttp >= 200 && $codigoHttp < 300 && empty($errorCurl)) {
            $datosRespuesta = json_decode($respuesta, true);
            if (isset($datosRespuesta['error'])) {
                $mensajeError = $datosRespuesta['error'];
                $esExitoso = false;
            } else {
                $esExitoso = true;
            }
        } else {
            if ($respuesta) {
                $datosRespuesta = json_decode($respuesta, true);
                if (isset($datosRespuesta['error'])) {
                    $mensajeError = $datosRespuesta['error'];
                } elseif (isset($datosRespuesta['message'])) {
                    $mensajeError = $datosRespuesta['message'];
                } else {
                    $mensajeError = $errorCurl ?: 'Error al enviar el mensaje';
                }
            } else {
                $mensajeError = $errorCurl ?: 'Error al enviar el mensaje';
            }
        }
        
    } catch (Exception $e) {
        $mensajeError = $e->getMessage();
        $esExitoso = false;
    } catch (Error $e) {
        $mensajeError = 'Error fatal: ' . $e->getMessage();
        $esExitoso = false;
    }

    if ($esExitoso) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?exito=1', true, 302);
        exit;
    } else {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?error=' . urlencode($mensajeError), true, 302);
        exit;
    }
}

// ============================================================================
// SECCIÓN 3: HTML DEL FORMULARIO
// ============================================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enviar Notificación WhatsApp</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="logo">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.77.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.873.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" fill="currentColor"/>
        </svg>
      </div>
      <h1>Enviar Notificación WhatsApp</h1>
      <p class="subtitle">Sistema de envío de mensajes</p>
    </div>

    <form id="formularioNotificacion" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
      <div class="form-section">
        <div class="form-group">
          <label for="telefono">Número de Teléfono <span class="required">*</span></label>
          <input 
            type="tel" 
            id="telefono" 
            name="phone_number" 
            pattern="\d{10}" 
            maxlength="10"
            required
          >
          <small class="help-text">Ingrese 10 dígitos sin código de país</small>
        </div>
      </div>

      <div class="form-section">
        <h3 class="section-title">
          <span class="icon">💬</span>
          Información de la Cita
        </h3>

        <div class="info-box" style="background: #e3f2fd; border-left: 4px solid var(--color-principal); padding: 15px; border-radius: var(--radio-pequeno); margin-bottom: 20px; font-size: 13px;">
          <strong>Plantilla:</strong> "Hola {{1}}, confirmamos tu cita con {{2}} el {{3}} a las {{4}}. Consultorio: {{5}}. Llega 15 minutos antes"
        </div>

        <div class="form-group">
          <label for="nombrePaciente">Nombre del Paciente <span class="required">*</span></label>
          <input 
            type="text" 
            id="nombrePaciente" 
            name="patient_name"
            required
          >
          <small class="help-text">Parámetro {{1}} de la plantilla</small>
        </div>

        <div class="form-group">
          <label for="nombreDoctor">Nombre del Doctor <span class="required">*</span></label>
          <input 
            type="text" 
            id="nombreDoctor" 
            name="doctor_name"
            required
          >
          <small class="help-text">Parámetro {{2}} de la plantilla</small>
        </div>

        <div class="form-group">
          <label for="fechaCita">Fecha de la Cita <span class="required">*</span></label>
          <input 
            type="text" 
            id="fechaCita" 
            name="appointment_date"
            required
          >
          <small class="help-text">Parámetro {{3}} de la plantilla</small>
        </div>

        <div class="form-group">
          <label for="horaCita">Hora de la Cita <span class="required">*</span></label>
          <input 
            type="text" 
            id="horaCita" 
            name="appointment_time"
            required
          >
          <small class="help-text">Parámetro {{4}} de la plantilla</small>
        </div>

        <div class="form-group">
          <label for="numeroConsultorio">Número de Consultorio <span class="required">*</span></label>
          <input 
            type="text" 
            id="numeroConsultorio" 
            name="consultory_number"
            required
          >
          <small class="help-text">Parámetro {{5}} de la plantilla</small>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary">
          <span class="btn-icon">📤</span>
          Enviar Notificación
        </button>
        <button type="reset" class="btn-secondary">
          Limpiar Formulario
        </button>
      </div>
    </form>
  </div>

  <!-- ===================================================================== -->
  <!-- SECCIÓN 4: JAVASCRIPT -->
  <!-- ===================================================================== -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const formulario = document.getElementById('formularioNotificacion');
      const inputTelefono = document.getElementById('telefono');
      
      if (!formulario || !inputTelefono) {
        console.error('Elementos del formulario no encontrados');
        return;
      }
      
      inputTelefono.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
        if (e.target.value.length > 10) {
          e.target.value = e.target.value.slice(0, 10);
        }
      });

      formulario.addEventListener('submit', function(e) {
        const telefono = inputTelefono.value.trim();
        
        if (telefono.length !== 10) {
          e.preventDefault();
          mostrarToast('Por favor, ingrese un número de teléfono válido de 10 dígitos', 'error');
          inputTelefono.focus();
          return false;
        }

        const camposRequeridos = [
          { id: 'nombrePaciente', nombre: 'Nombre del paciente' },
          { id: 'nombreDoctor', nombre: 'Nombre del doctor' },
          { id: 'fechaCita', nombre: 'Fecha de la cita' },
          { id: 'horaCita', nombre: 'Hora de la cita' },
          { id: 'numeroConsultorio', nombre: 'Número de consultorio' }
        ];

        for (let campo of camposRequeridos) {
          const input = document.getElementById(campo.id);
          if (!input || input.value.trim() === '') {
            e.preventDefault();
            mostrarToast(`Por favor, ingrese ${campo.nombre.toLowerCase()}`, 'error');
            if (input) input.focus();
            return false;
          }
        }

        const botonEnviar = formulario.querySelector('button[type="submit"]');
        if (botonEnviar) {
          botonEnviar.disabled = true;
          botonEnviar.innerHTML = '<span class="btn-icon">⏳</span> Enviando...';
        }
      });

      const inputsRequeridos = formulario.querySelectorAll('input[required]');
      inputsRequeridos.forEach(input => {
        input.addEventListener('blur', function() {
          if (this.value.trim() === '') {
            this.style.borderColor = 'var(--color-error)';
          } else {
            this.style.borderColor = 'var(--color-borde)';
          }
        });

        input.addEventListener('input', function() {
          if (this.value.trim() !== '') {
            this.style.borderColor = 'var(--color-borde)';
          }
        });
      });

      verificarParametrosURL();
    });

    function verificarParametrosURL() {
      const urlParams = new URLSearchParams(window.location.search);
      const exito = urlParams.get('exito');
      const error = urlParams.get('error');
      
      if (exito === '1') {
        mostrarToast('Mensaje enviado correctamente', 'exito');
        const formulario = document.getElementById('formularioNotificacion');
        if (formulario) {
          formulario.reset();
        }
        window.history.replaceState({}, document.title, window.location.pathname);
      } else if (error) {
        const mensajeError = decodeURIComponent(error);
        mostrarToast(mensajeError, 'error');
        window.history.replaceState({}, document.title, window.location.pathname);
      }
    }

    function mostrarToast(mensaje, tipo) {
      const toastExistente = document.querySelector('.toast');
      if (toastExistente) {
        toastExistente.remove();
      }

      const toast = document.createElement('div');
      toast.className = `toast ${tipo}`;
      toast.innerHTML = `
        <span class="toast-icon">${tipo === 'exito' ? '✅' : '❌'}</span>
        <span>${mensaje}</span>
      `;
      
      document.body.appendChild(toast);

      setTimeout(() => {
        toast.classList.add('ocultando');
        setTimeout(() => {
          if (toast.parentNode) {
            toast.remove();
          }
        }, 300);
      }, 4000);
    }
  </script>
</body>
</html>

