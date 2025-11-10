<?php
// Versión de prueba simple que SIEMPRE muestra algo
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Test Send</title>
  <style>
    body { font-family: Arial; padding: 20px; background: #f0f0f0; }
    .box { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; }
    .success { border-left: 4px solid green; }
    .error { border-left: 4px solid red; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
  </style>
</head>
<body>
  <div class="box success">
    <h1>✅ ARCHIVO send_test.php EJECUTADO CORRECTAMENTE</h1>
    <p>Si ves este mensaje, significa que el archivo PHP se está ejecutando en Vercel.</p>
  </div>
  
  <div class="box">
    <h2>Información de la Petición:</h2>
    <p><strong>Método HTTP:</strong> <?php echo $_SERVER['REQUEST_METHOD']; ?></p>
    <p><strong>URL:</strong> <?php echo $_SERVER['REQUEST_URI']; ?></p>
    <p><strong>POST recibido:</strong> <?php echo !empty($_POST) ? 'SÍ (' . count($_POST) . ' campos)' : 'NO'; ?></p>
  </div>
  
  <?php if (!empty($_POST)): ?>
  <div class="box success">
    <h2>Datos POST Recibidos:</h2>
    <pre><?php print_r($_POST); ?></pre>
  </div>
  <?php else: ?>
  <div class="box error">
    <h2>⚠️ NO SE RECIBIERON DATOS POST</h2>
    <p>El formulario no está enviando los datos correctamente.</p>
  </div>
  <?php endif; ?>
  
  <div class="box">
    <h2>Prueba de Configuración:</h2>
    <?php
    try {
      $config = require __DIR__ . '/config.php';
      echo "<p>✅ Config cargado correctamente</p>";
      echo "<pre>";
      echo "API URL: " . ($config['api_url'] ?? 'No definida') . "\n";
      echo "API Key: " . (isset($config['api_key']) ? substr($config['api_key'], 0, 10) . '...' : 'No definida') . "\n";
      echo "Internal ID: " . ($config['internal_id'] ?? 'No definida') . "\n";
      echo "</pre>";
    } catch (Exception $e) {
      echo "<p>❌ Error cargando config: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
  </div>
  
  <div class="box">
    <a href="/" style="display: inline-block; padding: 10px 20px; background: #004aad; color: white; text-decoration: none; border-radius: 5px;">← Volver al Formulario</a>
  </div>
</body>
</html>

