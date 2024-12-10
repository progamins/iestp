// Crear elemento video
const video = document.createElement("video");

// Nuestro canvas
const canvasElement = document.getElementById("qr-canvas");
const canvas = canvasElement.getContext("2d");

// Div donde llegará nuestro canvas
const btnScanQR = document.getElementById("btn-scan-qr");

// Lectura activada/desactivada
let scanning = false;

// Función para encender la cámara
const encenderCamara = () => {
  navigator.mediaDevices
    .getUserMedia({ video: { facingMode: "environment" } })
    .then(function (stream) {
      scanning = true;
      btnScanQR.hidden = true;
      canvasElement.hidden = false;
      video.setAttribute("playsinline", true); // requerido para iOS safari, evitando pantalla completa
      video.srcObject = stream;
      video.play();
      tick();
      scan();
    })
    .catch((error) => {
      console.error("Error al acceder a la cámara: ", error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo acceder a la cámara. Asegúrate de haber dado los permisos necesarios.',
      });
    });
};

// Función para actualizar el canvas con la imagen de la cámara
function tick() {
  if (video.readyState === video.HAVE_ENOUGH_DATA) {
    canvasElement.height = video.videoHeight;
    canvasElement.width = video.videoWidth;
    canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
  }

  scanning && requestAnimationFrame(tick);
}

// Función para decodificar el QR
function scan() {
  try {
    qrcode.decode();
  } catch (e) {
    setTimeout(scan, 300); // Reintentar escanear cada 300ms
  }
}

// Función para reproducir el sonido al escanear
const activarSonido = () => {
  var audio = document.getElementById('audioScaner');
  audio.play();
};

// Callback cuando termina de leer el código QR
qrcode.callback = (respuesta) => {
  if (respuesta) {
    const decodedResponse = decodeURIComponent(escape(respuesta));
    
    const datos = decodedResponse.split(", ");
    const dni = datos.find(dato => dato.startsWith('DNI:')).split(': ')[1];
    const programa = datos.find(dato => dato.startsWith('Programa:')).split(': ')[1];

    Swal.fire({
      title: 'Bienvenido',
      text: `DNI: ${dni}\nPrograma: ${programa}`,
      icon: 'success',
      showConfirmButton: false,  // No mostrar botón de confirmación
      timer: 2000  // Cerrar automáticamente después de 4 segundos
    }).then(() => {
      // Mantener la cámara activa después del escaneo para otros usuarios
      scan();  // Continuar escaneando más códigos QR
    });
    
    activarSonido();
    registrarAsistencia(dni); // Registrar la asistencia usando el DNI extraído
  }
};

// Función para registrar la asistencia
const registrarAsistencia = (dni_estudiante) => {
  const horario_id = 1; // Ejemplo de horario ID

  fetch('registrar_asistencia.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json; charset=UTF-8'
    },
    body: JSON.stringify({
        dni_estudiante: dni_estudiante,
        horario_id: horario_id
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log("Asistencia registrada con éxito");
    } else {
      console.error("Error al registrar asistencia: ", data.message);
    }
  });
};

// Activar la cámara automáticamente al cargar la página
window.addEventListener('load', () => {
  encenderCamara();
});
