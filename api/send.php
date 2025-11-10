<?php

header('Content-Type: text/html; charset=utf-8');

$mensajeError = '';
$esExitoso = false;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /?error=' . urlencode('Método no permitido'));
        exit;
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
        $esExitoso = true;
    } else {
        $mensajeError = $errorCurl ?: 'Error al enviar el mensaje';
    }
    
} catch (Exception $e) {
    $mensajeError = $e->getMessage();
    $esExitoso = false;
} catch (Error $e) {
    $mensajeError = 'Error fatal: ' . $e->getMessage();
    $esExitoso = false;
}

if ($esExitoso) {
    header('Location: /?exito=1');
} else {
    header('Location: /?error=' . urlencode($mensajeError));
}
exit;
