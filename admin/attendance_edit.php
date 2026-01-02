<?php
    include 'includes/session.php';
    include 'includes/conn.php';
    date_default_timezone_set('America/Bogota');
    setlocale(LC_TIME, 'es_CO.UTF-8');

    if (isset($_POST['edit'])) {

        // Recibir datos del formulario
        $id = $_POST['id'];
        $edit_date = $_POST['edit_date'];
        $edit_time_in = $_POST['edit_time_in'];
        $edit_time_in = date('H:i', strtotime($edit_time_in));
        $edit_time_out = $_POST['edit_time_out'];
        $edit_time_out = date('H:i', strtotime($edit_time_out));
        $diaCompleto = date('l');
        $abreviatura = strftime('%a');
        $festivo = ($_POST['festivo']!='dom') ? $abreviatura: $_POST['festivo'];  // 'dom' si es festivo, de lo contrario, por ejemplo, 'sab'
        $entry = $_POST['entry'];     // "1" o "2" (primera o segunda entrada)
        
        // Auxilio de transporte prorrateado (solo se asigna en la primera entrada)
        define('AUXILIO_TRANSPORTE_DIA', 6666);  // (200000/30)
        $auxilio_transporte = ($entry == '1') ? AUXILIO_TRANSPORTE_DIA : 0;

        // Verificar que el registro a editar exista
        $sql = "SELECT * FROM attendance WHERE id = '$id'";
        $query = $conn->query($sql);
        if ($query->num_rows <= 0) {
            echo "<script>alert('Registro no encontrado.'); window.location.href='attendance.php';</script>";
            exit;
        }
        $row = $query->fetch_assoc();

        // Calcular total de horas trabajadas (en horas decimales)
        $timeInObj = new DateTime($edit_time_in);
        $timeOutObj = new DateTime($edit_time_out);
        if ($timeOutObj < $timeInObj) {
            $timeOutObj->modify('+1 day');
        }
        $totalInterval = $timeOutObj->getTimestamp() - $timeInObj->getTimestamp();
        $total_horas = round($totalInterval / 3600, 2);

        // Obtener detalle de horas (separa en normales y extras, diurnas y nocturnas)
        $horasDetalle = calcularHorasDetalle($edit_time_in, $edit_time_out);
        // Por ejemplo, $horasDetalle['normal_diurnas'], ['normal_nocturnas'], ['extra_diurnas'], ['extra_nocturnas']

        // Calcular componentes salariales (aplicando tarifas según si es festivo o no)
        // Para días normales:
        //    - Hora ordinaria diurna: COP 6,189
        //    - Hora ordinaria nocturna: COP 8,355
        //    - Hora extra diurna: COP 7,736
        //    - Hora extra nocturna: COP 10,831
        // Para días festivos ('dom'):
        //    - Hora ordinaria diurna: COP 10,831
        //    - Hora ordinaria nocturna: COP 12,997
        //    - Hora extra diurna: COP 12,378
        //    - Hora extra nocturna: COP 15,472
        $detalles = calcularSalario($auxilio_transporte, $edit_time_in, $edit_time_out, $edit_date, $festivo, $horasDetalle);

        // Imprimir el resumen para depuración
        echo "<h3>Resumen del Cálculo</h3>";
        echo "ID Registro: $id<br>";
        echo "Fecha: $edit_date<br>";
        echo "Hora de Entrada: $edit_time_in<br>";
        echo "Hora de Salida: $edit_time_out<br>";
        echo "Festivo: " . (($festivo === 'dom') ? "Sí" : "No") . "<br>";
        echo "Entrada: $entry<br>";
        echo "Total Horas Trabajadas: $total_horas<br>";
        echo "Detalle de Horas: <pre>" . print_r($horasDetalle, true) . "</pre>";
        echo "Salario Base (horas ordinarias): " . $detalles['salario_base'] . " COP<br>";
        echo "Horas Extra Diurnas (hora_ext_diu): " . $detalles['hora_ext_diu'] . " COP<br>";
        echo "Horas Extra Nocturnas (hora_ext_dom_noc): " . $detalles['hora_ext_dom_noc'] . " COP<br>";
        echo "Auxilio de Transporte (aux_tran): " . $auxilio_transporte . " COP<br>";
        echo "Subtotal Remuneración (total): " . $detalles['total'] . " COP<br>";

        // Una vez verificado el resumen, se puede actualizar el registro.
        // Descomenta el siguiente bloque para proceder con la actualización en la base de datos.
        
        $sql = "UPDATE attendance SET 
                    date = '$edit_date', 
                    time_in = '$edit_time_in', 
                    salidas = '$edit_time_out', 
                    time_out = '$edit_time_out', 
                    dia = '$festivo', 
                    entradas = '$entry',
                    num_hr = $total_horas,
                    aux_tran = $auxilio_transporte,
                    salario_base = '{$detalles['salario_base']}', 
                    hora_ext_diu = '{$detalles['hora_ext_diu']}',
                    hora_ext_noc = '{$detalles['hora_ext_dom_noc']}',
                    total = '{$detalles['total']}'
                WHERE id = '$id'";
        if ($conn->query($sql)) {
            echo "<script>alert('Registro actualizado correctamente. Salario recalculado.'); window.location.href='attendance.php';</script>";
        } else {
            echo "<script>alert('Error al actualizar registro: " . $conn->error . "'); window.location.href='attendance.php';</script>";
        }
        
        exit;
    }
    else{
        echo "<script>alert('Complete el formulario de edición'); window.location.href='attendance.php';</script>";
    }

    /* ===================================================
       Funciones para el cálculo de horas y salario
       =================================================== */

    /**
     * Calcula la intersección (en segundos) del intervalo con el período diurno.
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
     * Se utiliza computeDiurnalNocturnal para dividir en horas diurnas y nocturnas.
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
        
        // PERIODO NORMAL: las primeras 8 horas
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
     * Calcula el salario y sus componentes basándose en el detalle de horas.
     *
     * Se aplican las siguientes tarifas para 2025:
     *
     * Día normal:
     *   - Hora ordinaria diurna: COP 6,189
     *   - Hora ordinaria nocturna: COP 8,355
     *   - Hora extra diurna: COP 7,736
     *   - Hora extra nocturna: COP 10,831
     *
     * Día festivo/dominical (si $festivo === 'dom'):
     *   - Hora ordinaria diurna: COP 10,831
     *   - Hora ordinaria nocturna: COP 12,997
     *   - Hora extra diurna: COP 12,378
     *   - Hora extra nocturna: COP 15,472
     *
     * Los campos que se retornan son:
     *   - salario_base: Pago de las horas ordinarias (normal_diurnas y normal_nocturnas)
     *   - hora_ext_diu: Pago de las horas extra diurnas
     *   - hora_ext_dom_noc: Pago de las horas extra nocturnas
     *   - aux_tran: Auxilio de transporte prorrateado (aplicado solo en la primera entrada)
     *   - total: Subtotal de remuneración
     *
     * @param float  $auxilio_transporte Auxilio de transporte (solo en la primera entrada)
     * @param string $time_in            Hora de entrada
     * @param string $time_out           Hora de salida
     * @param string $fecha              Fecha del turno
     * @param string $festivo            Indicador de festivo ('dom' para festivo, de lo contrario otro valor)
     * @param array  $horasDetalle       Detalle de horas calculado
     * @return array Componentes salariales y total
     */
    function calcularSalario($auxilio_transporte, $time_in, $time_out, $fecha, $festivo, $horasDetalle) {
        // Seleccionar tarifas según si es festivo o no
        if ($festivo === 'dom') {
            $tarifa_ordinaria_diurna = 10831;
            $tarifa_ordinaria_nocturna = 12997;
            $tarifa_extra_diurna = 12378;
            $tarifa_extra_nocturna = 15472;
        } else {
            $tarifa_ordinaria_diurna = 6189;
            $tarifa_ordinaria_nocturna = 8355;
            $tarifa_extra_diurna = 7736;
            $tarifa_extra_nocturna = 10831;
        }
        
        // Extraer los componentes del detalle de horas
        $normal_diurnas = $horasDetalle['normal_diurnas'];
        $normal_nocturnas = $horasDetalle['normal_nocturnas'];
        $extra_diurnas = $horasDetalle['extra_diurnas'];
        $extra_nocturnas = $horasDetalle['extra_nocturnas'];
        
        // Calcular el pago de las horas ordinarias (normal)
        $pago_normal_diurno = $normal_diurnas * $tarifa_ordinaria_diurna;
        $pago_normal_nocturno = $normal_nocturnas * $tarifa_ordinaria_nocturna;
        $pago_normal = $pago_normal_diurno + $pago_normal_nocturno;
        
        // Calcular el pago de las horas extra
        $pago_extra_diurno = $extra_diurnas * $tarifa_extra_diurna;
        $pago_extra_nocturno = $extra_nocturnas * $tarifa_extra_nocturna;
        
        // Calcular el total a pagar
        $total = $pago_normal + $pago_extra_diurno + $pago_extra_nocturno + $auxilio_transporte;
        
        return [
            'salario_base'     => round($pago_normal, 2),
            'hora_ext_diu'     => round($pago_extra_diurno, 2),
            'hora_ext_dom_noc' => round($pago_extra_nocturno, 2),
            'aux_tran'         => $auxilio_transporte,
            'total'            => round($total, 2)
        ];
    }
?>
