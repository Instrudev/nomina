<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Asistencia</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono&display=swap" rel="stylesheet">
  <style>
    /* Reset y fuente */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Roboto Mono', monospace;
      background: linear-gradient(135deg, #141E30, #243B55);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    /* Contenedor principal con dos columnas */
    .container {
      display: flex;
      width: 90%;
      max-width: 1200px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    /* Contenedor del video (lado izquierdo) */
    .video-container {
      flex: 1;
      position: relative;
      background: #000;
    }
    .video-container video {
      width: 50%;
      height: 50%;
      object-fit: cover;
    }
    /* Contenedor del login (lado derecho) */
    .login-container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
    }
    /* Caja de login con efecto glassmorphism */
    .login-box {
      width: 100%;
      max-width: 350px;
      text-align: center;
      padding: 20px;
      border-radius: 15px;
      background: rgba(255, 255, 255, 0.1);
      box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
      border: 1px solid rgba(255,255,255,0.18);
    }
    .login-logo p {
      margin: 5px 0;
      color: #fff;
    }
    .login-box-body {
      color: #fff;
    }
    h4.login-box-msg {
      font-weight: 300;
      margin-bottom: 30px;
      letter-spacing: 2px;
    }
    /* Estilos de formularios */
    .form-control {
      width: 100%;
      padding: 12px 15px;
      margin-bottom: 20px;
      border: 1px solid rgba(255,255,255,0.5);
      border-radius: 5px;
      background: rgba(255,255,255,0.2);
      color: black;
      font-size: 1em;
    }
    .form-control::placeholder {
      color: #e0e0e0;
    }
    .form-control:focus {
      outline: none;
      box-shadow: 0 0 5px rgba(255,255,255,0.5);
    }
    /* Estilo especial para el select */
    select.form-control {
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
      background-image: url('data:image/svg+xml;utf8,<svg fill="%23fff" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
      background-repeat: no-repeat;
      background-position: right 10px center;
      background-size: 24px;
    }
    /* Botón moderno */
    button.btn {
      background: linear-gradient(45deg, #ff6b6b, #f06595);
      border: none;
      padding: 12px 20px;
      border-radius: 5px;
      font-size: 1em;
      cursor: pointer;
      color: #fff;
      transition: background 0.3s ease;
      width: 100%;
    }
    button.btn:hover {
      background: linear-gradient(45deg, #f06595, #ff6b6b);
    }
    .bold { font-weight: 700; }
    /* Contenedor para mensajes de sesión */
    .message-box {
      background: rgba(0,0,0,0.5);
      border: 1px solid rgba(255,255,255,0.3);
      color: #fff;
      padding: 15px 20px;
      border-radius: 10px;
      margin-bottom: 20px;
      text-align: center;
      font-size: 1em;
      backdrop-filter: blur(5px);
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Video en el lado izquierdo
    <div class="video-container">
      <video autoplay muted loop>
        <source src="ruta_al_video.mp4" type="video/mp4">
        Tu navegador no soporta videos.
      </video>
    </div>
     -->
    <!-- Login en el lado derecho -->
    <div class="login-container">
      <div class="login-box">
        <?php if(isset($_SESSION['message'])): ?>
          <div class="message-box">
            <?php 
              echo $_SESSION['message']; 
              unset($_SESSION['message']);
            ?>
          </div>
        <?php endif; ?>
        <!-- MENSAJE DE MORA -->
        <?php
        
         $config_file = __DIR__ . '/admin/popup_config.json';
$popup_active = true;
if (file_exists($config_file)) {
    $cfg = json_decode(file_get_contents($config_file), true);
    if (isset($cfg['active'])) {
        $popup_active = (bool) $cfg['active'];
        
    }
}
        
        if($popup_active == 1 ): ?>
          <div class="message-box" style="border-color: #f2a654; background: rgba(242,166,84,0.2); color: #f2a654;">
            <strong>Atención:</strong> Tu factura venció el 18/06/2025.
            Por favor regulariza tu pago para continuar utilizando el sistema.
          </div>
        <?php endif; ?>
        
        <div class="login-logo">
          <p id="date"></p>
          <p id="time" class="bold"></p>
        </div>
        <div class="login-box-body">
          <h4 class="login-box-msg">Ingrese su ID de Empleado</h4>
          <form id="attendance" action="attendance.php" method="POST">
            <div class="form-group">
              <select class="form-control" name="status">
                <option value="in">Hora de Entrada</option>
                <option value="out">Hora de Salida</option>
              </select>
            </div>
            <div class="form-group">
              <input type="number" class="form-control" id="employee" name="employee" placeholder="ID de Empleado" required>
            </div>
            <!-- Campo oculto para enviar automáticamente si es festivo -->
            <input type="hidden" name="festivo" id="festivo" value="">
            <button type="submit" class="btn" name="signin">
              <i class="fa fa-sign-in"></i> Login
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php include 'scripts.php'; ?>
  <!-- Incluimos moment.js para el manejo de fecha y hora -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
  <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
      setInterval(function() {
        var momentNow = moment();
        document.getElementById('date').innerHTML = momentNow.format('dddd').substring(0, 3).toUpperCase() + ' - ' + momentNow.format('MMMM DD, YYYY');
        document.getElementById('time').innerHTML = momentNow.format('hh:mm:ss A');
        document.getElementById('festivo').value = (momentNow.day() === 0) ? 'dom' : '';
      }, 1000);
    });
  </script>
  <script src="dist/js/foto.js"></script>
</body>
</html>
