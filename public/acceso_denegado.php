<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 403 - Acceso Denegado</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        /* Efecto de partículas en el fondo */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: rgba(255, 77, 77, 0.5);
            border-radius: 50%;
            animation: float 8s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }

        .content {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 2rem;
        }

        h1 {
            font-size: 4rem;
            font-weight: bold;
            color: #FF4D4D;
            text-transform: uppercase;
            margin-bottom: 1rem;
            text-shadow: 0 0 10px rgba(255, 77, 77, 0.5);
            animation: glitch 3s infinite;
        }

        @keyframes glitch {
            0% {
                text-shadow: 0 0 10px rgba(255, 77, 77, 0.5);
                transform: translate(0);
            }
            20% {
                text-shadow: -3px 0 #00ff00, 3px 0 #ff0000;
                transform: translate(-2px, 2px);
            }
            40% {
                text-shadow: 3px 0 #00ff00, -3px 0 #ff0000;
                transform: translate(2px, -2px);
            }
            60% {
                text-shadow: 0 0 10px rgba(255, 77, 77, 0.5);
                transform: translate(0);
            }
            100% {
                text-shadow: 0 0 10px rgba(255, 77, 77, 0.5);
                transform: translate(0);
            }
        }

        .error-code {
            font-size: 8rem;
            font-weight: 900;
            background: linear-gradient(45deg, #FF4D4D, #FFB485);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        .message {
            font-size: 1.5rem;
            color: #fff;
            margin: 2rem 0;
            opacity: 0;
            animation: fadeInUp 0.5s forwards;
            animation-delay: 0.5s;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .action-button {
            background: linear-gradient(45deg, #FF4D4D, #FFB485);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.2rem;
            color: white;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 77, 77, 0.4);
            position: relative;
            overflow: hidden;
        }

        .action-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 77, 77, 0.6);
        }

        .action-button::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%) rotate(45deg);
            }
            100% {
                transform: translateX(100%) rotate(45deg);
            }
        }

        .watermark {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.5);
            animation: fadeIn 1s forwards;
            animation-delay: 1s;
        }

        /* Efectos de hover en elementos */
        .hover-effect {
            transition: all 0.3s ease;
        }

        .hover-effect:hover {
            transform: scale(1.1);
            text-shadow: 0 0 20px rgba(255,255,255,0.5);
        }
    </style>
</head>
<body>
    <!-- Partículas de fondo -->
    <div class="particles">
        <script>
            for(let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + 'vw';
                particle.style.animationDelay = Math.random() * 5 + 's';
                document.querySelector('.particles').appendChild(particle);
            }
        </script>
    </div>

    <div class="content">
        <div class="error-code animate__animated animate__bounceIn">403</div>
        <h1 class="hover-effect">Acceso Denegado</h1>
        <p class="message">
            <strong>¡ALTO!</strong><br>
            No tienes los permisos necesarios para acceder a esta área.
        </p>
        <a href="https://wa.me/933826949" class="action-button animate__animated animate__pulse animate__infinite">
            Solicitar Acceso
        </a>
    </div>

    <div class="watermark hover-effect">
        Diseñado por: Edwin Raul Rosas Albinez
    </div>
</body>
</html>