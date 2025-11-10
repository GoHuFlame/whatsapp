document.addEventListener('DOMContentLoaded', function() {
  const formulario = document.getElementById('formularioNotificacion');
  const inputTelefono = document.getElementById('telefono');
  
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
      alert('Por favor, ingrese un número de teléfono válido de 10 dígitos');
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
        alert(`Por favor, ingrese ${campo.nombre.toLowerCase()}`);
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
});
