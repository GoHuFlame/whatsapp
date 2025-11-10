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
