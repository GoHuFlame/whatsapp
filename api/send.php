<?php
/**
 * Procesa el envío de notificaciones WhatsApp a través de la API
 * 
 * Estructura de datos enviada:
 * {
 *   "phone_number": "521XXXXXXXXXX",
 *   "internal_id": "ID_DE_LA_PLANTILLA",
 *   "template_params": ["param1", "param2", "param3", "param4", "param5"]
 * }
 */

header('Content-Type: text/html; charset=utf-8');

$configuracion = null;
$mensajeError = '';
$mostrarError = false;
$codigoHttp = 0;
$respuesta = '';
$errorCurl = '';
$datos = [];
$telefonoCompleto = '';
$infoCurl = [];
$datosRespuesta = null;
$mensajeRespuesta = '';
$esExitoso = false;
$telefono = '';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Esta página solo acepta peticiones POST');
    }
    
    $configuracion = require __DIR__ . '/config.php';
    
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
    
    foreach ($camposRequeridos as $campo => $mensajeError) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            throw new Exception($mensajeError);
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
    $infoCurl = curl_getinfo($ch);
    curl_close($ch);
    
    if ($respuesta) {
        $datosRespuesta = json_decode($respuesta, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($datosRespuesta)) {
            if (isset($datosRespuesta['message'])) {
                $mensajeRespuesta = $datosRespuesta['message'];
            }
            if (isset($datosRespuesta['info'])) {
                $mensajeRespuesta .= ' | Info: ' . json_encode($datosRespuesta['info']);
            }
        }
    }
    
    if ($codigoHttp >= 200 && $codigoHttp < 300 && empty($errorCurl)) {
        $esExitoso = true;
        if ($datosRespuesta && isset($datosRespuesta['message']) && stripos($datosRespuesta['message'], 'ok') !== false) {
            $esExitoso = true;
        }
    } else {
        $esExitoso = false;
    }
    
} catch (Exception $e) {
    $mensajeError = $e->getMessage();
    $mostrarError = true;
    $esExitoso = false;
} catch (Error $e) {
    $mensajeError = 'Error fatal: ' . $e->getMessage();
    $mostrarError = true;
    $esExitoso = false;
}

if (!isset($configuracion)) {
    $configuracion = ['api_url' => 'No configurada', 'api_key' => '', 'internal_id' => ''];
}
if (!isset($telefonoCompleto) && isset($telefono)) {
    $telefonoCompleto = '521' . $telefono;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resultado del Envío | Mercately</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <style>
    .result-container {
      max-width: 700px;
      margin: 40px auto;
      background: var(--surface);
      border-radius: var(--radius);
      box-shadow: var(--shadow-lg);
      padding: 40px;
    }
    .result-header {
      text-align: center;
      margin-bottom: 30px;
    }
    .result-icon {
      font-size: 64px;
      margin-bottom: 20px;
    }
    .result-title {
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 10px;
    }
    .result-message {
      color: var(--text-secondary);
      margin-bottom: 30px;
    }
    .response-box {
      background: var(--background);
      border-radius: var(--radius-sm);
      padding: 20px;
      margin-top: 20px;
      border-left: 4px solid var(--primary-color);
    }
    .response-box h4 {
      margin-bottom: 10px;
      color: var(--text-primary);
    }
    .response-box pre {
      background: #1e1e1e;
      color: #d4d4d4;
      padding: 15px;
      border-radius: var(--radius-sm);
      overflow-x: auto;
      font-size: 12px;
      line-height: 1.5;
      max-height: 400px;
      overflow-y: auto;
    }
    .btn-back {
      display: inline-block;
      margin-top: 30px;
      padding: 12px 24px;
      background: var(--primary-color);
      color: white;
      text-decoration: none;
      border-radius: var(--radius-sm);
      transition: all 0.3s ease;
    }
    .btn-back:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
    }
    .success { color: var(--success-color); }
    .error { color: var(--error-color); }
    .info-box {
      background: #e3f2fd;
      border-left: 4px solid var(--primary-color);
      padding: 15px;
      border-radius: var(--radius-sm);
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="result-container">
    <div class="result-header">
      <?php if ($esExitoso && !$mostrarError): ?>
        <div class="result-icon">✅</div>
        <h2 class="result-title success">Mensaje Enviado Correctamente</h2>
        <p class="result-message">La notificación de WhatsApp ha sido enviada exitosamente.</p>
        <?php if ($mensajeRespuesta): ?>
          <p style="font-size: 14px; color: var(--success-color); margin-top: 10px;">
            <strong>Respuesta:</strong> <?php echo htmlspecialchars($mensajeRespuesta); ?>
          </p>
        <?php endif; ?>
      <?php else: ?>
        <div class="result-icon">❌</div>
        <h2 class="result-title error"><?php echo $mostrarError ? 'Error de Validación' : 'Error al Enviar Mensaje'; ?></h2>
        <p class="result-message">
          <?php if ($mostrarError && $mensajeError): ?>
            <?php echo htmlspecialchars($mensajeError); ?>
          <?php elseif ($errorCurl): ?>
            Error de conexión: <?php echo htmlspecialchars($errorCurl); ?>
          <?php elseif ($codigoHttp > 0): ?>
            El servidor respondió con código HTTP <?php echo $codigoHttp; ?>
            <?php if ($mensajeRespuesta): ?>
              <br><strong>Mensaje:</strong> <?php echo htmlspecialchars($mensajeRespuesta); ?>
            <?php endif; ?>
          <?php else: ?>
            No se recibió respuesta del servidor
          <?php endif; ?>
        </p>
      <?php endif; ?>
    </div>

    <?php if ($telefonoCompleto || $telefono): ?>
    <div class="info-box">
      <strong>Información del Envío:</strong><br>
      <small>
        Número: <?php echo htmlspecialchars($telefonoCompleto ?: '521' . $telefono); ?><br>
        Código HTTP: <?php echo $codigoHttp; ?><br>
        <?php if (isset($infoCurl['connect_time']) && $infoCurl['connect_time']): ?>
          Tiempo de conexión: <?php echo number_format($infoCurl['connect_time'], 2); ?>s<br>
        <?php endif; ?>
        <?php if (isset($infoCurl['total_time']) && $infoCurl['total_time']): ?>
          Tiempo total: <?php echo number_format($infoCurl['total_time'], 2); ?>s
        <?php endif; ?>
      </small>
    </div>
    <?php endif; ?>

    <?php if ($respuesta): ?>
    <div class="response-box">
      <h4>Respuesta del Servidor:</h4>
      <pre><?php echo htmlspecialchars($respuesta); ?></pre>
    </div>
    <?php endif; ?>

    <?php if (!empty($datos)): ?>
    <div class="response-box">
      <h4>Datos Enviados a la API:</h4>
      <pre><?php echo htmlspecialchars(json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
    </div>
    <?php endif; ?>

    <div style="text-align: center;">
      <a href="/" class="btn-back">← Volver al Formulario</a>
    </div>
  </div>
</body>
</html>
