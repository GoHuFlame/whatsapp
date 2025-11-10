<?php
// Iniciar buffer de salida para capturar cualquier error
ob_start();

// Verificar que es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

$config = require __DIR__ . '/config.php';

// Validar que la configuración esté completa
if (empty($config['api_url']) || empty($config['api_key']) || empty($config['internal_id'])) {
    $errorMessage = 'Error de configuración: Faltan variables de entorno. Verifique MERCATELY_API_URL, MERCATELY_API_KEY y MERCATELY_INTERNAL_ID en Vercel';
    $showError = true;
    $httpCode = 0;
    $response = '';
    $curlError = $errorMessage;
    $data = [];
    $fullPhone = '';
    $curlInfo = [];
    $responseData = null;
    $responseMessage = '';
    $isSuccess = false;
    goto render_page;
}

// Validar que se recibió el número de teléfono
if (!isset($_POST['phone_number']) || empty(trim($_POST['phone_number']))) {
    $errorMessage = 'El número de teléfono es requerido';
    $showError = true;
    $httpCode = 0;
    $response = '';
    $curlError = $errorMessage;
    $data = [];
    $fullPhone = '';
    $curlInfo = [];
    $responseData = null;
    $responseMessage = '';
    $isSuccess = false;
    goto render_page;
}

// Limpiar número y validar formato
$phone = preg_replace('/\D/', '', $_POST['phone_number']);
if (strlen($phone) !== 10) {
    $errorMessage = 'El número debe tener exactamente 10 dígitos';
    $showError = true;
    $httpCode = 0;
    $response = '';
    $curlError = $errorMessage;
    $data = [];
    $fullPhone = '';
    $curlInfo = [];
    $responseData = null;
    $responseMessage = '';
    $isSuccess = false;
    goto render_page;
}

// Agregar prefijo de México (521)
$fullPhone = '521' . $phone;

// Validar y recopilar parámetros de plantilla en el orden correcto
$templateParams = [];

// Inicializar variables
$showError = false;
$errorMessage = '';

if (!isset($_POST['patient_name']) || empty(trim($_POST['patient_name']))) {
    $errorMessage = 'El nombre del paciente es requerido';
    $showError = true;
    goto prepare_error;
}
$templateParams[] = trim($_POST['patient_name']);

if (!isset($_POST['doctor_name']) || empty(trim($_POST['doctor_name']))) {
    $errorMessage = 'El nombre del doctor es requerido';
    $showError = true;
    goto prepare_error;
}
$templateParams[] = trim($_POST['doctor_name']);

if (!isset($_POST['appointment_date']) || empty(trim($_POST['appointment_date']))) {
    $errorMessage = 'La fecha de la cita es requerida';
    $showError = true;
    goto prepare_error;
}
$templateParams[] = trim($_POST['appointment_date']);

if (!isset($_POST['appointment_time']) || empty(trim($_POST['appointment_time']))) {
    $errorMessage = 'La hora de la cita es requerida';
    $showError = true;
    goto prepare_error;
}
$templateParams[] = trim($_POST['appointment_time']);

if (!isset($_POST['consultory_number']) || empty(trim($_POST['consultory_number']))) {
    $errorMessage = 'El número de consultorio es requerido';
    $showError = true;
    goto prepare_error;
}
$templateParams[] = trim($_POST['consultory_number']);

// Si llegamos aquí, continuar con el envío
goto send_request;

prepare_error:
    $httpCode = 0;
    $response = '';
    $curlError = $errorMessage;
    $data = [];
    $fullPhone = '521' . $phone;
    $curlInfo = [];
    $responseData = null;
    $responseMessage = '';
    $isSuccess = false;
    goto render_page;

send_request:

// Construir estructura de datos EXACTAMENTE igual que test_connection.php
$data = [
    "phone_number" => $fullPhone,
    "internal_id" => $config['internal_id'],
    "template_params" => $templateParams
];

// Enviar petición - configuración idéntica a test_connection.php
$ch = curl_init($config['api_url']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'api-key: ' . $config['api_key'],
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlInfo = curl_getinfo($ch);
curl_close($ch);

// Parsear respuesta JSON si existe
$responseData = null;
$responseMessage = '';
$isSuccess = false;

if ($response) {
    $responseData = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($responseData)) {
        // La respuesta es JSON válido
        if (isset($responseData['message'])) {
            $responseMessage = $responseData['message'];
        }
        if (isset($responseData['info'])) {
            $responseMessage .= ' | Info: ' . json_encode($responseData['info']);
        }
    }
}

// Determinar si fue exitoso
if ($httpCode >= 200 && $httpCode < 300 && empty($curlError)) {
    $isSuccess = true;
    // Verificar si la respuesta indica éxito
    if ($responseData && isset($responseData['message']) && stripos($responseData['message'], 'ok') !== false) {
        $isSuccess = true;
    }
} else {
    $isSuccess = false;
}

render_page:
// Limpiar cualquier salida previa
ob_clean();
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
    .warning-box {
      background: #fff3cd;
      border-left: 4px solid #ffc107;
      padding: 15px;
      border-radius: var(--radius-sm);
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="result-container">
    <div class="result-header">
      <?php if ($isSuccess && !$showError): ?>
        <div class="result-icon">✅</div>
        <h2 class="result-title success">Mensaje Enviado Correctamente</h2>
        <p class="result-message">La notificación de WhatsApp ha sido enviada exitosamente.</p>
        <?php if ($responseMessage): ?>
          <p style="font-size: 14px; color: var(--success-color); margin-top: 10px;">
            <strong>Respuesta:</strong> <?php echo htmlspecialchars($responseMessage); ?>
          </p>
        <?php endif; ?>
      <?php else: ?>
        <div class="result-icon">❌</div>
        <h2 class="result-title error"><?php echo $showError ? 'Error de Validación' : 'Error al Enviar Mensaje'; ?></h2>
        <p class="result-message">
          <?php if ($showError && $errorMessage): ?>
            <?php echo htmlspecialchars($errorMessage); ?>
          <?php elseif ($curlError && $curlError !== 'Ninguno'): ?>
            Error de conexión: <?php echo htmlspecialchars($curlError); ?>
          <?php elseif ($httpCode > 0): ?>
            El servidor respondió con código HTTP <?php echo $httpCode; ?>
            <?php if ($responseMessage): ?>
              <br><strong>Mensaje:</strong> <?php echo htmlspecialchars($responseMessage); ?>
            <?php endif; ?>
          <?php else: ?>
            No se recibió respuesta del servidor
          <?php endif; ?>
        </p>
      <?php endif; ?>
    </div>

    <div class="info-box">
      <strong>Información del Envío:</strong><br>
      <small>
        Número: <?php echo htmlspecialchars($fullPhone); ?><br>
        Código HTTP: <?php echo $httpCode; ?><br>
        <?php if ($curlInfo['connect_time']): ?>
          Tiempo de conexión: <?php echo number_format($curlInfo['connect_time'], 2); ?>s<br>
        <?php endif; ?>
        <?php if ($curlInfo['total_time']): ?>
          Tiempo total: <?php echo number_format($curlInfo['total_time'], 2); ?>s
        <?php endif; ?>
      </small>
    </div>

    <div class="response-box">
      <h4>Respuesta Completa del Servidor:</h4>
      <pre><?php echo htmlspecialchars($response ?: 'Sin respuesta'); ?></pre>
    </div>

    <div class="response-box">
      <h4>Datos Enviados a la API:</h4>
      <pre><?php echo htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
    </div>

    <?php if ($responseData): ?>
    <div class="response-box">
      <h4>Respuesta Parseada (JSON):</h4>
      <pre><?php echo htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
    </div>
    <?php endif; ?>

    <?php if (!$isSuccess): ?>
    <div class="warning-box">
      <h4>⚠️ Información de Depuración:</h4>
      <pre><?php 
        $debugInfo = [
          'url' => $config['api_url'],
          'http_code' => $httpCode,
          'curl_error' => $curlError ?: 'Ninguno',
          'connect_time' => $curlInfo['connect_time'] ?? 'N/A',
          'total_time' => $curlInfo['total_time'] ?? 'N/A',
          'primary_ip' => $curlInfo['primary_ip'] ?? 'N/A',
          'response_length' => strlen($response ?? '')
        ];
        echo htmlspecialchars(json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); 
      ?></pre>
      <p style="margin-top: 10px; font-size: 13px;">
        <strong>Si el código HTTP es 200 pero el mensaje no llega:</strong><br>
        • Verifique que el número de teléfono sea válido y esté registrado en WhatsApp<br>
        • Verifique que la plantilla (internal_id) esté aprobada y activa<br>
        • Revise la respuesta del servidor arriba para ver si hay mensajes de error específicos<br>
        • Contacte al soporte de Mercately con el código HTTP y la respuesta completa
      </p>
    </div>
    <?php endif; ?>

    <div style="text-align: center;">
      <a href="/" class="btn-back">← Volver al Formulario</a>
    </div>
  </div>
</body>
</html>
