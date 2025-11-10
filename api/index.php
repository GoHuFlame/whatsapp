<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enviar Notificación WhatsApp | Mercately</title>
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
      <p class="subtitle">Sistema de envío de mensajes Mercately</p>
    </div>

    <form id="notificationForm" action="/api/send.php" method="POST">
      <div class="form-section">
        <div class="form-group">
          <label for="phone_number">Número de Teléfono <span class="required">*</span></label>
          <input 
            type="tel" 
            id="phone_number" 
            name="phone_number" 
            placeholder="5512345678" 
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

        <div class="info-box" style="background: #e3f2fd; border-left: 4px solid var(--primary-color); padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 13px;">
          <strong>Plantilla:</strong> "Hola {{1}}, confirmamos tu cita con {{2}} el {{3}} a las {{4}}. Consultorio: {{5}}. Llega 15 minutos antes"
        </div>

        <div class="form-group">
          <label for="patient_name">Nombre del Paciente <span class="required">*</span></label>
          <input 
            type="text" 
            id="patient_name" 
            name="patient_name" 
            placeholder="Ej: Juan Pérez"
            required
          >
          <small class="help-text">Este será el parámetro {{1}} de la plantilla</small>
        </div>

        <div class="form-group">
          <label for="doctor_name">Nombre del Doctor <span class="required">*</span></label>
          <input 
            type="text" 
            id="doctor_name" 
            name="doctor_name" 
            placeholder="Ej: Dra. María López"
            required
          >
          <small class="help-text">Este será el parámetro {{2}} de la plantilla</small>
        </div>

        <div class="form-group">
          <label for="appointment_date">Fecha de la Cita <span class="required">*</span></label>
          <input 
            type="text" 
            id="appointment_date" 
            name="appointment_date" 
            placeholder="Ej: 10 de noviembre"
            required
          >
          <small class="help-text">Este será el parámetro {{3}} de la plantilla</small>
        </div>

        <div class="form-group">
          <label for="appointment_time">Hora de la Cita <span class="required">*</span></label>
          <input 
            type="text" 
            id="appointment_time" 
            name="appointment_time" 
            placeholder="Ej: 3:30 PM"
            required
          >
          <small class="help-text">Este será el parámetro {{4}} de la plantilla</small>
        </div>

        <div class="form-group">
          <label for="consultory_number">Número de Consultorio <span class="required">*</span></label>
          <input 
            type="text" 
            id="consultory_number" 
            name="consultory_number" 
            placeholder="Ej: Consultorio 5"
            required
          >
          <small class="help-text">Este será el parámetro {{5}} de la plantilla</small>
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

  <script src="/assets/js/main.js"></script>
</body>
</html>
