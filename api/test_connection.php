<?php
/**
 * Script de prueba de conectividad con la API de Mercately
 * Ejecute este archivo para diagnosticar problemas de conexión
 */

$config = require __DIR__ . '/config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test de Conectividad - Mercately</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;} .test{background:white;padding:15px;margin:10px 0;border-radius:8px;border-left:4px solid #004aad;} .success{border-left-color:#10b981;} .error{border-left-color:#ef4444;} pre{background:#f0f0f0;padding:10px;border-radius:5px;overflow-x:auto;}</style></head><body>";
echo "<h1>🔍 Test de Conectividad - API Mercately</h1>";

// Test 1: Resolución DNS
echo "<div class='test'>";
echo "<h2>1. Test de Resolución DNS</h2>";
$host = 'app.mercately.com';
$ip = gethostbyname($host);
if ($ip !== $host) {
    echo "<p class='success'>✅ DNS resuelve correctamente: <strong>$ip</strong></p>";
} else {
    echo "<p class='error'>❌ Error: No se puede resolver el DNS de $host</p>";
}
echo "</div>";

// Test 2: Conexión básica
echo "<div class='test'>";
echo "<h2>2. Test de Conexión HTTPS</h2>";
$testUrl = 'https://app.mercately.com';
$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlInfo = curl_getinfo($ch);
curl_close($ch);

if ($httpCode > 0) {
    echo "<p class='success'>✅ Conexión HTTPS exitosa. Código HTTP: <strong>$httpCode</strong></p>";
} else {
    echo "<p class='error'>❌ Error de conexión: <strong>$curlError</strong></p>";
}
echo "<pre>Info: " . print_r($curlInfo, true) . "</pre>";
echo "</div>";

// Test 3: Endpoint específico
echo "<div class='test'>";
echo "<h2>3. Test del Endpoint de API</h2>";
echo "<p>URL: <strong>{$config['api_url']}</strong></p>";

$testData = [
    "phone_number" => "5219611527266",
    "internal_id" => $config['internal_id'],
    "template_params" => ["test", "test", "test", "test", "test"]
];

$ch = curl_init($config['api_url']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'api-key: ' . $config['api_key'],
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
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

echo "<p><strong>Datos enviados:</strong></p>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

if ($httpCode > 0) {
    echo "<p class='success'>✅ Respuesta recibida. Código HTTP: <strong>$httpCode</strong></p>";
    echo "<p><strong>Respuesta:</strong></p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
} else {
    echo "<p class='error'>❌ Error: <strong>$curlError</strong></p>";
    echo "<p><strong>Información de conexión:</strong></p>";
    echo "<pre>";
    echo "Connect Time: " . ($curlInfo['connect_time'] ?? 'N/A') . "s\n";
    echo "Total Time: " . ($curlInfo['total_time'] ?? 'N/A') . "s\n";
    echo "Primary IP: " . ($curlInfo['primary_ip'] ?? 'N/A') . "\n";
    echo "Name Lookup Time: " . ($curlInfo['namelookup_time'] ?? 'N/A') . "s\n";
    echo "</pre>";
}

echo "</div>";

// Test 4: Información del servidor
echo "<div class='test'>";
echo "<h2>4. Información del Servidor PHP</h2>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "cURL disponible: " . (function_exists('curl_version') ? 'Sí' : 'No') . "\n";
if (function_exists('curl_version')) {
    $curlVersion = curl_version();
    echo "cURL Version: " . $curlVersion['version'] . "\n";
    echo "SSL Version: " . $curlVersion['ssl_version'] . "\n";
}
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'Habilitado' : 'Deshabilitado') . "\n";
echo "</pre>";
echo "</div>";

echo "<div class='test'>";
echo "<h2>📝 Resumen</h2>";
if ($httpCode > 0) {
    echo "<p class='success'>✅ La conexión básica funciona. El problema puede estar en:</p>";
    echo "<ul>";
    echo "<li>El formato de los datos enviados</li>";
    echo "<li>La API key o permisos</li>";
    echo "<li>El endpoint específico</li>";
    echo "</ul>";
} else {
    echo "<p class='error'>❌ No se puede establecer conexión. Posibles causas:</p>";
    echo "<ul>";
    echo "<li>Firewall bloqueando conexiones salientes</li>";
    echo "<li>El hosting no permite conexiones HTTPS salientes</li>";
    echo "<li>Problemas de DNS o red</li>";
    echo "<li>El servidor de Mercately no está accesible desde este hosting</li>";
    echo "</ul>";
    echo "<p><strong>Recomendación:</strong> Contacte al soporte de su hosting y al soporte de Mercately.</p>";
}
echo "</div>";

echo "</body></html>";
?>


