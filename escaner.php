<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escáner de Código QR - Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="assets/plugins/qrCode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/escaner.css">
</head>
<body>
    <div class="background"></div>
    <img src="images/logo.png" alt="Logo" class="logo">
    
    <div class="container">
        <div class="lector-qr">
            <h5 class="text-center mb-3">Escanear Código QR de Asistencia</h5>
            <div class="row text-center">
                <div class="col-12">
                    <a id="btn-scan-qr" href="#">
                        <img src="https://dab1nmslvvntp.cloudfront.net/wp-content/uploads/2017/07/1499401426qr_icon.svg" 
                             class="img-fluid" width="175" alt="QR Scanner">
                    </a>
                    <canvas hidden="" id="qr-canvas" class="img-fluid"></canvas>
                </div>
                <div class="col-12 mt-3">
                    <div id="status-message" class="alert d-none"></div>
                </div>
            </div>
        </div>
    </div>

    <audio id="audioScaner" src="assets/sonido.mp3"></audio>
    
    <script>
       class QRScanner {
    constructor() {
        this.video = document.createElement("video");
        this.canvasElement = document.getElementById("qr-canvas");
        this.canvas = this.canvasElement.getContext("2d");
        this.btnScanQR = document.getElementById("btn-scan-qr");
        this.statusMessage = document.getElementById("status-message");
        this.scanning = false;
        this.currentHorarioId = null;
        this.setupQRCallback();
        this.initializeScanner();
        this.getCurrentHorario();
    }

    // Agregar método para obtener el horario actual
    async getCurrentHorario() {
        try {
            const response = await fetch('get_horario_actual.php');
            const data = await response.json();
            if (data.success && data.horario_id) {
                this.currentHorarioId = data.horario_id;
            } else {
                this.showMessage('warning', 'No hay horario configurado');
            }
        } catch (error) {
            console.error('Error al obtener horario:', error);
            this.showMessage('danger', 'Error al obtener horario');
        }
    }

            initializeScanner() {
                window.addEventListener('load', () => this.startCamera());
            }

            startCamera() {
                navigator.mediaDevices
                    .getUserMedia({ video: { facingMode: "environment" } })
                    .then((stream) => {
                        this.scanning = true;
                        this.btnScanQR.hidden = true;
                        this.canvasElement.hidden = false;
                        this.video.setAttribute("playsinline", true);
                        this.video.srcObject = stream;
                        this.video.play();
                        this.tick();
                        this.scan();
                    })
                    .catch(this.handleCameraError.bind(this));
            }

            handleCameraError(error) {
                console.error("Error de cámara:", error);
                this.showMessage('error', 'No se pudo acceder a la cámara. Verifique los permisos.');
            }

            tick() {
                if (this.video.readyState === this.video.HAVE_ENOUGH_DATA) {
                    this.canvasElement.height = this.video.videoHeight;
                    this.canvasElement.width = this.video.videoWidth;
                    this.canvas.drawImage(this.video, 0, 0, this.canvasElement.width, this.canvasElement.height);
                }
                if (this.scanning) {
                    requestAnimationFrame(this.tick.bind(this));
                }
            }

            scan() {
                try {
                    qrcode.decode();
                } catch (e) {
                    setTimeout(this.scan.bind(this), 300);
                }
            }

            playSound() {
                const audio = document.getElementById('audioScaner');
                audio.play().catch(console.error);
            }

            showMessage(type, message) {
                this.statusMessage.className = `alert alert-${type}`;
                this.statusMessage.textContent = message;
                this.statusMessage.classList.remove('d-none');
                setTimeout(() => this.statusMessage.classList.add('d-none'), 3000);
            }

            parseQRData(qrContent) {
                try {
                    return JSON.parse(qrContent);
                } catch (e) {
                    // Intenta parsear el formato antiguo (DNI: xxx, Programa: xxx)
                    const datos = qrContent.split(", ");
                    const dni = datos.find(dato => dato.startsWith('DNI:')).split(': ')[1];
                    const programa = datos.find(dato => dato.startsWith('Programa:')).split(': ')[1];
                    return { dni_estudiante: dni, programa: programa };
                }
            }

            async registerAttendance(data) {
        try {
            if (!this.currentHorarioId) {
                throw new Error('No hay horario configurado');
            }

            const response = await fetch('registrar_asistencia.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    dni_estudiante: data.dni_estudiante,
                    horario_id: this.currentHorarioId,
                    programa: data.programa
                })
            });

            const result = await response.json();
            
            if (result.success) {
                this.showMessage('success', result.message || 'Asistencia registrada con éxito');
                this.playSound();
            } else {
                this.showMessage('warning', result.message || 'Error al registrar asistencia');
            }
        } catch (error) {
            this.showMessage('danger', error.message || 'Error de conexión con el servidor');
            console.error('Error:', error);
        }

        setTimeout(() => this.scan(), 1000);
    }
            setupQRCallback() {
                qrcode.callback = (response) => {
                    if (response) {
                        try {
                            const decodedResponse = decodeURIComponent(escape(response));
                            const data = this.parseQRData(decodedResponse);
                            
                            if (!data.dni_estudiante) {
                                throw new Error('QR inválido - No contiene DNI');
                            }

                            this.registerAttendance(data);
                        } catch (error) {
                            this.showMessage('danger', 'QR inválido o mal formado');
                            console.error('Error al procesar QR:', error);
                            setTimeout(() => this.scan(), 1000);
                        }
                    }
                };
            }
        }

        // Inicializar el scanner
        const scanner = new QRScanner();
    </script>
</body>
</html>