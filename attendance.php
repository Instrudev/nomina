<?php  

include 'conn.php';
include 'timezone.php';
date_default_timezone_set('America/Bogota');
session_start();
if (isset($_POST['employee'])) {

    // Definiciones para 2025
    define('SALARIO_MINIMO', 1423500); // Salario mínimo legal mensual 2025
    define('AUXILIO_TRANSPORTE_DIA', 6667); // Auxilio prorrateado (200000/30)

    setlocale(LC_TIME, 'es_ES.UTF-8');
    $employee = $_POST['employee'];
    $status = $_POST['status'];

    // Obtener empleado
    $sql = "SELECT * FROM employees WHERE employee_id = '$employee'";
    $query = $conn->query($sql);

    if ($query->num_rows > 0) {
        $row = $query->fetch_assoc();
        $id = $row['id'];
        $firstname= $row['firstname'];
        $date_now = date('Y-m-d');
        $time_now = date('H:i');

        if ($status == 'in') {
            manejarEntrada($conn, $id,$firstname, $date_now, $time_now);
        } elseif ($status == 'out') {
            manejarSalida($conn, $id, $date_now, $time_now);
        }
    } else {
        $_SESSION['message'] = "Empleado no encontrado.";
        //echo "<script>alert('Empleado no encontrado.'); window.location.href='index.php';</script>";
    }
}

/**
 * Verifica si existe un registro de entrada sin salida para el empleado en la fecha actual.
 */
function validar($conn, $employee_id, $date_now) {
    $sql = "SELECT COUNT(*) AS total FROM attendance WHERE employee_id = '$employee_id' AND date = '$date_now' AND time_out IS NULL";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();
    return ($row['total'] > 0);
}

/**
 * Registra la entrada del empleado.
 * Se controla que solo se permitan dos entradas por día y se asigna el auxilio de transporte únicamente en la primera entrada.
 */
function manejarEntrada($conn, $employee_id,$firstname, $date_now, $time_now) {
    $sql = "SELECT * FROM attendance WHERE employee_id = '$employee_id' AND date = '$date_now'";
    $query = $conn->query($sql);
    $entrada_numero = $query->num_rows + 1;
    
    // Si ya existe una entrada sin salida, no se permite registrar una nueva entrada
    if (validar($conn, $employee_id, $date_now)) {
        $_SESSION['message'] = "No puedes registrar una nueva entrada sin haber registrado la salida de la entrada anterior";
        echo "<script> window.location.href='index.php';</script>";
        return;
    }
    
    // Solo se permiten dos entradas por día
    if ($entrada_numero > 2) {
        $_SESSION['message'] = "De acuerdo a las reglas de la empresa, solo puedes registrar dos entradas por día.";
        echo "<script> window.location.href='index.php';</script>";
        return;
    }
    
    // Asignar auxilio de transporte solo en la primera entrada
    $auxilio_transporte = ($entrada_numero === 1) ? AUXILIO_TRANSPORTE_DIA : 0;

    $sql = "INSERT INTO attendance (employee_id, date, time_in, entradas, aux_tran) 
            VALUES ('$employee_id', '$date_now', '$time_now', $entrada_numero, $auxilio_transporte)";
    if ($conn->query($sql)) {
        $_SESSION['message'] = "Entrada N-$entrada_numero $firstname registrada correctamente..";
        echo "<script> window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Error al registrar entrada: " . $conn->error . "'); window.location.href='index.php';</script>";
    }
}

/**
 * Calcula la intersección (en segundos) de un intervalo con el período diurno.
 * Se asume que el período diurno es de 06:00 a 21:00.
 *
 * @param DateTime $start Inicio del intervalo
 * @param DateTime $end   Fin del intervalo
 * @return array [$diurnalSeconds, $nocturnalSeconds]
 */
function computeDiurnalNocturnal($start, $end) {
    $diurnalSeconds = 0;
    $temp = clone $start;
    while ($temp < $end) {
        $currentDay = $temp->format('Y-m-d');
        $dStart = new DateTime($currentDay . ' 06:00:00');
        $dEnd = new DateTime($currentDay . ' 21:00:00');

        $intervalStart = max($temp->getTimestamp(), $dStart->getTimestamp());
        $intervalEnd = min($end->getTimestamp(), $dEnd->getTimestamp());
        if ($intervalEnd > $intervalStart) {
            $diurnalSeconds += ($intervalEnd - $intervalStart);
        }
        // Avanzar al inicio del siguiente día
        $temp = new DateTime($currentDay . ' 23:59:59');
        $temp->modify('+1 second');
    }
    $totalSeconds = $end->getTimestamp() - $start->getTimestamp();
    $nocturnalSeconds = $totalSeconds - $diurnalSeconds;
    return [$diurnalSeconds, $nocturnalSeconds];
}

/**
 * Calcula el detalle de las horas trabajadas dividiendo el turno en dos períodos:
 * - PERIODO NORMAL: Las primeras 8 horas desde la entrada.
 * - PERIODO EXTRA: Todo lo que exceda las 8 horas.
 *
 * @param string $time_in  Hora de entrada (compatible con DateTime)
 * @param string $time_out Hora de salida (compatible con DateTime)
 * @return array Detalle de horas en formato decimal
 */
function calcularHorasDetalle($time_in, $time_out) {
    $entrada = new DateTime($time_in);
    $salida = new DateTime($time_out);
    if ($salida < $entrada) {
        $salida->modify('+1 day');
    }
    
    $totalSeconds = $salida->getTimestamp() - $entrada->getTimestamp();
    $total_hours = $totalSeconds / 3600;
    
    // PERIODO NORMAL: primeras 8 horas desde la entrada
    $normalEnd = clone $entrada;
    $normalEnd->modify('+8 hours');
    if ($normalEnd > $salida) {
        $normalEnd = clone $salida;
    }
    list($diurnalNormalSec, $nocturnalNormalSec) = computeDiurnalNocturnal($entrada, $normalEnd);
    $normal_diurnas = $diurnalNormalSec / 3600;
    $normal_nocturnas = $nocturnalNormalSec / 3600;
    
    // PERIODO EXTRA: desde el fin del período normal hasta la salida
    $extra_diurnas = 0;
    $extra_nocturnas = 0;
    if ($salida > $normalEnd) {
        list($diurnalExtraSec, $nocturnalExtraSec) = computeDiurnalNocturnal($normalEnd, $salida);
        $extra_diurnas = $diurnalExtraSec / 3600;
        $extra_nocturnas = $nocturnalExtraSec / 3600;
    }
    
    return [
        'normal_diurnas'   => round($normal_diurnas, 2),
        'normal_nocturnas' => round($normal_nocturnas, 2),
        'extra_diurnas'    => round($extra_diurnas, 2),
        'extra_nocturnas'  => round($extra_nocturnas, 2),
        'total_normal'     => round(($normalEnd->getTimestamp() - $entrada->getTimestamp()) / 3600, 2),
        'total_extra'      => round(($salida->getTimestamp() - $normalEnd->getTimestamp()) / 3600, 2),
        'total'            => round($total_hours, 2)
    ];
}

/**
 * Registra la salida del empleado.
 * Busca el último registro abierto (sin time_out) del día actual, calcula las horas trabajadas y sus componentes salariales,
 * e imprime un resumen del cálculo para verificar los datos.
 */
function manejarSalida($conn, $employee_id, $date_now, $time_now) {
    $sql = "SELECT * FROM attendance WHERE employee_id = '$employee_id' AND date = '$date_now' ORDER BY entradas DESC LIMIT 1";
    $query = $conn->query($sql);
    if ($query->num_rows > 0 && validar($conn, $employee_id, $date_now)) {
        $row = $query->fetch_assoc();
        $time_in = $row['time_in'];
      
        // Calcular detalles salariales
        $detalles = calcularSalario($row['aux_tran'], $time_in, $time_now, $date_now);
        
        // Calcular total de horas trabajadas y obtener el detalle de horas
        $horasDetalle = calcularHorasDetalle($time_in, $time_now);
        $total_horas = $horasDetalle['total'];
        $dia = strftime('%A');
        
        // Imprimir el resumen del cálculo para verificación
        /*
        echo "<h3>Resumen del Cálculo de Asistencia</h3>";
        echo "<strong>Fecha:</strong> $date_now<br>";
        echo "<strong>Hora de Entrada:</strong> $time_in<br>";
        echo "<strong>Hora de Salida:</strong> $time_now<br>";
        echo "<strong>Detalle de Horas:</strong> <pre>" . print_r($horasDetalle, true) . "</pre>";
        echo "<strong>Componentes Salariales:</strong> <pre>" . print_r($detalles, true) . "</pre>";
        echo "<strong>Total Horas Trabajadas:</strong> $total_horas<br>";
        */
        // Nota: Aquí se imprime el resumen para verificar el cálculo.
        // Cuando se confirme que todo es correcto, se puede descomentar el siguiente bloque para actualizar la base de datos.
        
        $sql = "UPDATE attendance SET 
                    dia = '$dia',
                    time_out = '$time_now', 
                    num_hr = $total_horas,
                    salario_base = '{$detalles['salario_base']}', 
                    hora_ext_diu = '{$detalles['hora_ext_diu']}', 
                    hora_ext_noc = '{$detalles['hora_ext_noc']}', 
                    total = '{$detalles['total']}'
                WHERE id = '{$row['id']}'";
        if ($conn->query($sql)) {
             $_SESSION['message'] = "Salida registrada correctamente. Salario recalculado..";
            echo "<script> window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Error al registrar salida: " . $conn->error . "'); window.location.href='index.php';</script>";
        }
       
    } else {
        $_SESSION['message'] = "No hay entradas pendientes para salida.";
        echo "<script>window.location.href='index.php';</script>";
    }
}

/**
 * Calcula el salario y sus componentes basado en el detalle de horas trabajadas.
 *
 * Valores para 2025:
 * 
 * Día normal:
 *   - Hora ordinaria diurna: COP 6,189
 *   - Hora ordinaria nocturna: COP 8,355
 *   - Hora extra diurna: COP 7,736
 *   - Hora extra nocturna: COP 10,831
 * 
 * Día festivo/dominical:
 *   - Hora ordinaria diurna: COP 10,831
 *   - Hora ordinaria nocturna: COP 12,997
 *   - Hora extra diurna: COP 12,378
 *   - Hora extra nocturna: COP 15,472
 *
 * @param float  $auxilio_transporte Auxilio de transporte (se aplica solo en la primera entrada)
 * @param string $time_in            Hora de entrada
 * @param string $time_out           Hora de salida
 * @param string $fecha              Fecha del turno
 * @return array Componentes salariales y total
 */
function calcularSalario($auxilio_transporte, $time_in, $time_out, $fecha) {
    // Lista de fechas festivas
    $festivos = [
        "2025-01-30", "2025-01-05", "2025-01-06", "2025-01-12", "2025-01-19", "2025-01-26", 
        "2025-02-02", "2025-02-09", "2025-02-16", "2025-02-21", "2025-03-02", "2025-03-09", 
        "2025-03-16", "2025-03-23","2025-03-24", "2025-03-30", "2025-04-06", "2025-04-13", 
        "2025-04-17", "2025-04-18", "2025-04-20", "2025-04-27", "2025-05-01", "2025-05-04", 
        "2025-05-11", "2025-05-18", "2025-05-25", "2025-05-26", "2025-06-01", "2025-06-08", 
        "2025-06-15", "2025-06-22", "2025-06-23", "2025-06-29", "2025-06-30", "2025-07-06", 
        "2025-07-13", "2025-07-20", "2025-07-27", "2025-08-03", "2025-08-07", "2025-08-10", 
        "2025-08-17", "2025-08-18", "2025-08-24", "2025-08-31", "2025-09-07", "2025-09-14", 
        "2025-09-21", "2025-09-28", "2025-10-05", "2025-10-12", "2025-10-13", "2025-10-19", 
        "2025-10-26", "2025-11-02", "2025-11-03", "2025-11-09", "2025-11-16", "2025-11-17", 
        "2025-11-23", "2025-11-30", "2025-12-07", "2025-12-08", "2025-12-14", "2025-12-21", 
        "2025-12-25", "2025-12-28"
    ];
    $esFestivo = in_array($fecha, $festivos);

    // Obtener detalle de horas trabajadas
    $detalleHoras = calcularHorasDetalle($time_in, $time_out);
    $normal_diurnas   = $detalleHoras['normal_diurnas'];
    $normal_nocturnas = $detalleHoras['normal_nocturnas'];
    $extra_diurnas    = $detalleHoras['extra_diurnas'];
    $extra_nocturnas  = $detalleHoras['extra_nocturnas'];

    // Asignar tarifas según si es día festivo o no
    if (!$esFestivo) {
        // Día normal:
        $hora_ordinaria_diurna = 6189;
        $hora_ordinaria_nocturna = 8355;
        $hora_extra_diurna = 7736;
        $hora_extra_nocturna = 10831;
    } else {
        // Día festivo o dominical:
        $hora_ordinaria_diurna = 10831;
        $hora_ordinaria_nocturna = 12997;
        $hora_extra_diurna = 12378;
        $hora_extra_nocturna = 15472;
    }
    
    // Calcular pagos por cada componente
     $pago_normal_diurno = $normal_diurnas * $hora_ordinaria_diurna;
 $pago_normal_nocturno = $normal_nocturnas * $hora_ordinaria_nocturna;
     $pago_extra_diurno = $extra_diurnas * $hora_extra_diurna;
    echo $pago_extra_nocturno = $extra_nocturnas * $hora_extra_nocturna;

    $total = $pago_normal_diurno + $pago_normal_nocturno + $pago_extra_diurno + $pago_extra_nocturno + $auxilio_transporte;
    
    // Calcular recargo festivo (opcional), comparando con la tarifa base de día normal
    $base_normal_total = ($normal_diurnas + $normal_nocturnas) * 6189; 
    $recargo_festivo = ($esFestivo) ? (($pago_normal_diurno + $pago_normal_nocturno) - $base_normal_total) : 0;

    return [
        'salario_base'     => round($pago_normal_diurno + $pago_normal_nocturno, 2),
        'recargo_noc'      => round($pago_normal_nocturno, 2) - round($normal_nocturnas * 6189, 2),
        'hora_ext_diu'     => ($esFestivo) ? round($pago_extra_diurno, 2) : round($pago_extra_diurno, 2),
        'hora_ext_noc'     => ($esFestivo) ? round($pago_extra_nocturno, 2) : round($pago_extra_nocturno, 2),
        //'hora_ext_dom_diu' => ($esFestivo) ? round($pago_extra_diurno, 2) : 0,
        //'hora_ext_dom_noc' => ($esFestivo) ? round($pago_extra_nocturno, 2) : 0,
        //'festivo'          => round($recargo_festivo, 2),
        'total'            => round($total, 2)
    ];
}
?>
