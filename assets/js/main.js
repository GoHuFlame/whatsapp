document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('notificationForm');
  const phoneInput = document.getElementById('phone_number');
  
  // Validar y formatear número de teléfono
  phoneInput.addEventListener('input', function(e) {
    // Solo permitir números
    e.target.value = e.target.value.replace(/\D/g, '');
    
    // Limitar a 10 dígitos
    if (e.target.value.length > 10) {
      e.target.value = e.target.value.slice(0, 10);
    }
  });

  // Validación del formulario antes de enviar
  form.addEventListener('submit', function(e) {
    const phone = phoneInput.value.trim();
    
    // Validar número de teléfono
    if (phone.length !== 10) {
      e.preventDefault();
      alert('Por favor, ingrese un número de teléfono válido de 10 dígitos');
      phoneInput.focus();
      return false;
    }

    // Validar campos requeridos de la cita
    const requiredFields = [
      { id: 'patient_name', name: 'Nombre del paciente' },
      { id: 'doctor_name', name: 'Nombre del doctor' },
      { id: 'appointment_date', name: 'Fecha de la cita' },
      { id: 'appointment_time', name: 'Hora de la cita' },
      { id: 'consultory_number', name: 'Número de consultorio' }
    ];

    for (let field of requiredFields) {
      const input = document.getElementById(field.id);
      if (!input || input.value.trim() === '') {
        e.preventDefault();
        alert(`Por favor, ingrese ${field.name.toLowerCase()}`);
        if (input) input.focus();
        return false;
      }
    }

    // Mostrar estado de carga
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="btn-icon">⏳</span> Enviando...';
  });

  // Validación en tiempo real para campos requeridos
  const requiredInputs = form.querySelectorAll('input[required], textarea[required]');
  requiredInputs.forEach(input => {
    input.addEventListener('blur', function() {
      if (this.value.trim() === '') {
        this.style.borderColor = 'var(--error-color)';
      } else {
        this.style.borderColor = 'var(--border-color)';
      }
    });

    input.addEventListener('input', function() {
      if (this.value.trim() !== '') {
        this.style.borderColor = 'var(--border-color)';
      }
    });
  });

  // Validación de email
  const emailInput = document.getElementById('email');
  if (emailInput) {
    emailInput.addEventListener('blur', function() {
      const email = this.value.trim();
      if (email && !isValidEmail(email)) {
        this.style.borderColor = 'var(--error-color)';
        showFieldError(this, 'Por favor, ingrese un correo electrónico válido');
      } else {
        this.style.borderColor = 'var(--border-color)';
        hideFieldError(this);
      }
    });
  }

  // Validación de URL
  const urlInput = document.getElementById('media_url');
  if (urlInput) {
    urlInput.addEventListener('blur', function() {
      const url = this.value.trim();
      if (url && !isValidURL(url)) {
        this.style.borderColor = 'var(--error-color)';
        showFieldError(this, 'Por favor, ingrese una URL válida');
      } else {
        this.style.borderColor = 'var(--border-color)';
        hideFieldError(this);
      }
    });
  }
});

function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function isValidURL(url) {
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
}

function showFieldError(input, message) {
  hideFieldError(input);
  const errorDiv = document.createElement('div');
  errorDiv.className = 'field-error';
  errorDiv.textContent = message;
  errorDiv.style.color = 'var(--error-color)';
  errorDiv.style.fontSize = '12px';
  errorDiv.style.marginTop = '4px';
  input.parentNode.appendChild(errorDiv);
}

function hideFieldError(input) {
  const errorDiv = input.parentNode.querySelector('.field-error');
  if (errorDiv) {
    errorDiv.remove();
  }
}

