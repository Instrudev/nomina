<?php
// Establece la zona horaria a Colombia
date_default_timezone_set('America/Bogota');
// Obtiene la hora actual
$current_time = date('Y-m-d H:i:s');

// Muestra la hora actual
echo "La hora actual en Colombia es: " . $current_time;
?>