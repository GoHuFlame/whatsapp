<?php
$config = require __DIR__ . '/config.php';

// Validar que se recibió el número de teléfono
if (!isset($_POST['phone_number']) || empty(trim($_POST['phone_number']))) {
    die(json_encode([
        'success' => false,
        'error' => 'El número de teléfono es requerido'
    ]));
}

// Limpiar número y validar formato
$phone = preg_replace('/\D/', '', $_POST['phone_number']);
if (strlen($phone) !== 10) {
    die(json_encode([
        'success' => false,
        'error' => 'El número debe tener exactamente 10 dígitos'
    ]));
}

// Agregar prefijo de México (521) - probar sin el signo + primero
// Algunas APIs requieren el número sin el signo +
$fullPhone = '521' . $phone;

// Validar y recopilar parámetros de plantilla en el orden correcto
// Plantilla: "Hola {{1}}, confirmamos tu cita con {{2}} el {{3}} a las {{4}}. Consultorio: {{5}}"
$templateParams = [];

// Parámetro 1: Nombre del paciente
if (!isset($_POST['patient_name']) || empty(trim($_POST['patient_name']))) {
    die(json_encode([
        'success' => false,
        'error' => 'El nombre del paciente es requerido'
    ]));
}
$templateParams[] = trim($_POST['patient_name']);

// Parámetro 2: Nombre del doctor
if (!isset($_POST['doctor_name']) || empty(trim($_POST['doctor_name']))) {
    die(json_encode([
        'success' => false,
        'error' => 'El nombre del doctor es requerido'
    ]));
}
$templateParams[] = trim($_POST['doctor_name']);

// Parámetro 3: Fecha de la cita
if (!isset($_POST['appointment_date']) || empty(trim($_POST['appointment_date']))) {
    die(json_encode([
        'success' => false,
        'error' => 'La fecha de la cita es requerida'
    ]));
}
$templateParams[] = trim($_POST['appointment_date']);

// Parámetro 4: Hora de la cita
if (!isset($_POST['appointment_time']) || empty(trim($_POST['appointment_time']))) {
    die(json_encode([
        'success' => false,
        'error' => 'La hora de la cita es requerida'
    ]));
}
$templateParams[] = trim($_POST['appointment_time']);

// Parámetro 5: Número de consultorio
if (!isset($_POST['consultory_number']) || empty(trim($_POST['consultory_number']))) {
    die(json_encode([
        'success' => false,
        'error' => 'El número de consultorio es requerido'
    ]));
}
$templateParams[] = trim($_POST['consultory_number']);

// Construir estructura de datos según especificaciones
$data = [
    "phone_number" => $fullPhone,
    "internal_id" => $config['internal_id'],
    "template_params" => $templateParams
];

// Agregar campos opcionales si están presentes
if (!empty($_POST['media_url'])) {
    $data["media_url"] = trim($_POST['media_url']);
}

// Usar el nombre del paciente como first_name
if (!empty($templateParams[0])) {
    $data["first_name"] = $templateParams[0];
}

if (!empty($_POST['last_name'])) {
    $data["last_name"] = trim($_POST['last_name']);
}

if (!empty($_POST['email'])) {
    $data["email"] = trim($_POST['email']);
}

if (!empty($_POST['address'])) {
    $data["address"] = trim($_POST['address']);
}

if (!empty($_POST['city'])) {
    $data["city"] = trim($_POST['city']);
}

if (!empty($_POST['state'])) {
    $data["state"] = trim($_POST['state']);
}

if (!empty($_POST['zip_code'])) {
    $data["zip_code"] = trim($_POST['zip_code']);
}

if (!empty($_POST['notes'])) {
    $data["notes"] = trim($_POST['notes']);
}

if (isset($_POST['agent_id']) && $_POST['agent_id'] !== '') {
    $data["agent_id"] = (int)$_POST['agent_id'];
}

if (!empty($_POST['funnel_name'])) {
    $data["funnel_name"] = trim($_POST['funnel_name']);
}

if (!empty($_POST['stage'])) {
    $data["stage"] = trim($_POST['stage']);
}

// Preparar tags (si se necesitan en el futuro)
$tags = [];
if (!empty($data["first_name"])) {
    $tags[] = ["name" => "contacto", "value" => true];
}
if (!empty($data["email"])) {
    $tags[] = ["name" => "email_registrado", "value" => true];
}
if (!empty($tags)) {
    $data["tags"] = $tags;
}

// Preparar custom_fields (si se necesitan en el futuro)
// $customFields = [];
// if (!empty($customFields)) {
//     $data["custom_fields"] = $customFields;
// }

// Enviar petición
// Intentar primero con configuración estándar, si falla probar alternativas
$ch = curl_init($config['api_url']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'api-key: ' . $config['api_key'],
    'Accept: application/json',
    'Connection: close' // Cerrar conexión después de la petición
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_TIMEOUT, 90); // Timeout total de 90 segundos
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // Timeout de conexión
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verificar SSL
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // Verificar host SSL
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Seguir redirecciones
curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Máximo de redirecciones
curl_setopt($ch, CURLOPT_USERAGENT, 'Mercately-PHP-Client/1.0'); // User agent
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // Forzar IPv4
curl_setopt($ch, CURLOPT_VERBOSE, false); // Desactivar modo verbose para producción

// Configuraciones adicionales para hosts con restricciones
curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1); // Mantener conexión viva
curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 10);
curl_setopt($ch, CURLOPT_TCP_KEEPINTVL, 1);
curl_setopt($ch, CURLOPT_FRESH_CONNECT, true); // Forzar nueva conexión
curl_setopt($ch, CURLOPT_FORBID_REUSE, true); // No reutilizar conexión

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlInfo = curl_getinfo($ch);
curl_close($ch);

// Log para depuración (opcional, comentar en producción)
$debugInfo = [
    'url' => $config['api_url'],
    'http_code' => $httpCode,
    'curl_error' => $curlError,
    'curl_info' => [
        'total_time' => isset($curlInfo['total_time']) ? $curlInfo['total_time'] : null,
        'connect_time' => isset($curlInfo['connect_time']) ? $curlInfo['connect_time'] : null,
        'primary_ip' => isset($curlInfo['primary_ip']) ? $curlInfo['primary_ip'] : null,
        'namelookup_time' => isset($curlInfo['namelookup_time']) ? $curlInfo['namelookup_time'] : null,
        'http_code' => isset($curlInfo['http_code']) ? $curlInfo['http_code'] : null,
        'url' => isset($curlInfo['url']) ? $curlInfo['url'] : null,
    ],
    'dns_test' => gethostbyname('app.mercately.com') !== 'app.mercately.com' ? gethostbyname('app.mercately.com') : 'DNS no resuelve'
];

// Página de respuesta con diseño profesional
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
      <?php if ($httpCode >= 200 && $httpCode < 300 && empty($curlError)): ?>
        <div class="result-icon">✅</div>
        <h2 class="result-title success">Mensaje Enviado Correctamente</h2>
        <p class="result-message">La notificación de WhatsApp ha sido enviada exitosamente.</p>
      <?php else: ?>
        <div class="result-icon">❌</div>
        <h2 class="result-title error">Error al Enviar Mensaje</h2>
        <p class="result-message">
          <?php if ($curlError): ?>
            Error de conexión: <?php echo htmlspecialchars($curlError); ?>
          <?php else: ?>
            El servidor respondió con código HTTP <?php echo $httpCode; ?>
          <?php endif; ?>
        </p>
      <?php endif; ?>
    </div>

    <div class="info-box">
      <strong>Información del Envío:</strong><br>
      <small>Número: <?php echo htmlspecialchars($fullPhone); ?> | Código HTTP: <?php echo $httpCode; ?></small>
    </div>

    <div class="response-box">
      <h4>Respuesta del Servidor:</h4>
      <pre><?php echo htmlspecialchars($response ?: 'Sin respuesta'); ?></pre>
    </div>

    <div class="response-box">
      <h4>Datos Enviados:</h4>
      <pre><?php echo htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
    </div>

    <?php if (!empty($curlError) || $httpCode == 0): ?>
    <div class="response-box" style="border-left-color: var(--error-color);">
      <h4>Información de Depuración:</h4>
      <pre><?php echo htmlspecialchars(json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
      <p style="margin-top: 10px; font-size: 13px; color: var(--text-secondary);">
        <strong>Posibles causas:</strong><br>
        • El servidor de Mercately no está respondiendo o no es accesible desde este hosting<br>
        • Problemas de conectividad de red o firewall bloqueando la conexión<br>
        • El endpoint puede estar incorrecto o no disponible<br>
        • Problemas de DNS (no se puede resolver app.mercately.com)<br>
        • Verifique que la API key sea correcta y tenga permisos<br>
        • El servidor puede requerir conexiones desde IPs autorizadas
      </p>
      <p style="margin-top: 10px; font-size: 12px; color: var(--text-secondary); background: #fff3cd; padding: 10px; border-radius: 5px;">
        <strong>💡 Soluciones sugeridas:</strong><br>
        1. Verifique que el hosting tenga acceso saliente a HTTPS<br>
        2. Contacte al soporte de Mercately para verificar el endpoint y permisos<br>
        3. Pruebe la conexión desde otro servidor o localmente<br>
        4. Verifique si hay restricciones de firewall en el hosting
      </p>
    </div>
    <?php endif; ?>

    <div style="text-align: center;">
      <a href="/" class="btn-back">← Volver al Formulario</a>
    </div>
  </div>
</body>
</html>
