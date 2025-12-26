<?php
if (isset($_POST['employee'])) {
	$output = array('error' => false);

	include 'conn.php';
	include 'timezone.php';

	$employee = $_POST['employee'];
	$status = $_POST['status'];

	$sql = "SELECT * FROM employees WHERE employee_id = '$employee'";
	$query = $conn->query($sql);

	if ($query->num_rows > 0) {
		$row = $query->fetch_assoc();
		$id = $row['id'];

		$date_now = date('Y-m-d');
		$date = new DateTime('now');
		$time =  $date->format('H:i:s');

		if ($status == 'in') {
			$sql = "SELECT * FROM attendance WHERE employee_id = '$id' AND date = '$date_now' AND entradas=1 AND time_in IS NOT NULL";
			$query = $conn->query($sql);

			$entrada1 = $query->num_rows;
			// $sql2 =	"SELECT * FROM attendance WHERE employee_id = '$id' AND date = '$date_now' AND entradas=2 AND time_in IS NOT NULL";
			// $query2 = $conn->query($sql);
			// $entrada2 = $query2->num_rows;
			if ($entrada1 == 1) {
				$output['message'] = 'Has registrado tu entradas hoy';
			} else if ($entrada1 == 0) {
				$sched = $row['schedule_id'];
				$lognow = date('H:i:s');
				$sql = "SELECT * FROM schedules WHERE id = '$sched'";
				// trae el horario del empleado
				$squery = $conn->query($sql);
				$srow = $squery->fetch_assoc();
				setlocale(LC_TIME, "es_CO");
				$dia =  strftime('%a');
				$entrada = 1;
				$logstatus = ($lognow > $srow['time_in']) ? 0 : 1;
				//
				$sql = "INSERT INTO attendance (employee_id, date, dia, time_in, status, entradas) VALUES ('$id', '$date_now', '$dia', '22:00:00', '$logstatus', $entrada)";
				if ($conn->query($sql)) {
					$output['message'] = 'Llegada 1: ' . $row['firstname'] . ' ' . $row['lastname'];
				} else {
					$output['error'] = true;
					$output['message'] = $conn->error;
				}
			}
			// if ($entrada2 == 1) {
			// 	$output['message'] = 'Has registrado tu entrada N2 hoy';
			// }
			$sql = "SELECT * FROM attendance WHERE employee_id =  '$id' AND date = '$date_now' AND EXISTS(SELECT * FROM attendance WHERE entradas=1)  AND time_in IS NOT NULL";
			$query = $conn->query($sql);
			$attrow = $query->fetch_assoc();
			$entrada2 = $query->num_rows;

			if ($entrada2 == 1 && $attrow['time_out'] == '00:00:00') {

				// $output['message'] = 'Debes registrar la salida de entrada N1';

			} else if ($entrada2 == 1 && $attrow['time_out'] != '00:00:00') {
				$sched = $row['schedule_id'];
				$lognow = date('H:i:s');
				$sql = "SELECT * FROM schedules WHERE id = '$sched'";
				// trae el horario del empleado
				$squery = $conn->query($sql);
				$srow = $squery->fetch_assoc();
				setlocale(LC_TIME, "es_CO");
				$dia =  strftime('%a');
				$entrada = 2;
				$logstatus = ($lognow > $srow['time_in']) ? 0 : 1;

				$sql = "INSERT INTO attendance (employee_id, date, dia, time_in, status, entradas) VALUES ('$id', '$date_now', '$dia', '$time', '$logstatus', $entrada)";
				if ($conn->query($sql)) {
					$output['message'] = 'Llegada 2: ' . $row['firstname'] . ' ' . $row['lastname'];
				} else {
					$output['error'] = true;
					$output['message'] = $conn->error;
				}
			}
		} else if ($status == 'out') {
			$sql = "SELECT *, attendance.id AS uid FROM attendance LEFT JOIN employees ON employees.id=attendance.employee_id WHERE attendance.employee_id = '$id' AND entradas=1 AND date = '$date_now'";
			$query = $conn->query($sql);
			$salida1 = $query->num_rows;
			$row = $query->fetch_assoc();
			if ($salida1 == 1 && $row['time_out'] == '00:00:00') {
				$sql = "UPDATE attendance SET time_out = '06:00:00', salidas = 1 WHERE id = '" . $row['uid'] . "'  AND  entradas= 1";
				($sql) ? $output['message'] = 'salida 1: ' . $row['firstname'] . ' ' . $row['lastname'] : false;
				if ($conn->query($sql)) {
					$output['message'] = 'Salida: ' . $row['firstname'] . ' ' . $row['lastname'];

					$sql = "SELECT * FROM attendance WHERE id = '" . $row['uid'] . "'";
					$query = $conn->query($sql);
					$urow = $query->fetch_assoc();

					$time_in = $urow['time_in'];
					$time_out = $urow['time_out'];

					$sql = "SELECT * FROM employees LEFT JOIN schedules ON schedules.id=employees.schedule_id WHERE employees.id = '$id'";
					$query = $conn->query($sql);
					$srow = $query->fetch_assoc();


					$festivos = [
						'2021-05-17',
						'2021-06-07',
						'2021-06-14',
						'2021-07-05',
						'2021-07-20',
						'2021-08-07',
						'2021-08-16',
						'2021-10-18',
						'2021-11-01',
						'2021-11-15',
						'2021-12-08',
						'2021-12-25'
					];



					$salario_base = 1423500;
					$horas_trab = 50.64;
					$precio_hor =          round($salario_base / 240, 2);
					$dia_ord =             round(($salario_base / 240) * 8, 2);
					$festivo =             round($dia_ord * 0.75);
					$festivo_hor =         round($festivo / 8);
					$recargo_noc =         round($precio_hor * 0.35);
					$aux_trans = 5400;
					$hora_ext_diu =        ($salario_base * 1.25) / 240;
					$hora_ext_noc =        ($salario_base * 1.75) / 240;
					$hora_ext_dom_diu =    ($salario_base * 2) / 240;
					$hora_dom_noc =        ($salario_base * 2.1) / 240;
					$hora_ext_dom_noc =    ($salario_base * 2.5) / 240;
					$basico = $precio_hor * $horas_trab;
					$precios = [
						'salario_base' => $salario_base,
						'precio_hor' =>   $precio_hor,
						'precio_hor_noc' => $precio_hor + $recargo_noc,
						'dia_ord' =>      $dia_ord,
						'festivo' =>      $festivo,
						'festivo_hor' => $festivo_hor,
						'recargo_noc' =>  $recargo_noc,
						'aux_trans' =>    $aux_trans,
						'hora_ext_diu' =>       round($hora_ext_diu),
						'hora_ext_noc' =>       round($hora_ext_noc),
						'hora_ext_dom_diu' =>   round($hora_ext_dom_diu),
						'hora_dom_noc' =>       round($hora_dom_noc),
						'hora_ext_dom_noc' =>   round($hora_ext_dom_noc),
						'basico' => round($basico),
					];



					$jornada_ordinaria = [
						'entrada' => new DateTime('06:00:00'),
						'salida' => new DateTime('06:00:00'),
					];

					$jornada_nocturna = [
						'entrada' => new DateTime('22:00:00'),
						'salida' => new DateTime('23:59:00'),
					];



					$entrada = new DateTime($time_in);
					$salida = new DateTime($time_out);




					$inicio_noche   = new datetime('22:00:00');
					$fin_noche      = new datetime('06:00:00');
					$intermedia = new datetime('23:59:59');
					$medianoche     = new datetime('00:00:00');

					function nocturnas($entrada, $salida, $intermedia, $fin_noche, $medianoche)
					{



						if ($entrada >= $fin_noche &&  $entrada <= $intermedia) {
							return 0;
						} else if ($entrada >= $medianoche && $salida <= $fin_noche) {
							$calcular = $salida->diff($entrada);
							$horas = $calcular->format('%h');
							$minutos = $calcular->format('%i') / 60;
							$total  = number_format($horas + $minutos, 2);
							return $total;
						} else {
							return  0;
						}
					}

					$horas_noc = nocturnas($entrada, $salida, $intermedia, $fin_noche, $medianoche);
					echo $horas_noc;

					function salario($horas, $precios)
					{

						$calculo = $horas * $precios['precio_hor_noc'];
						$total = round($calculo, 2);
						return $total;
					}


					function recargo_nocturno($horas, $precios)
					{

						$calculo = $horas * $precios['recargo_noc'];
						$total = round($calculo, 2);
						return $total;
					}


					$recargo_nocturno = recargo_nocturno($horas_noc, $precios);



					function festivos($festivos, $fecha, $precios, $dia, $horas)
					{
						$horas_festivos = array();

						for ($i = 0; $i < count($festivos); $i++) {
							// echo $festivos[$i];
							if ($festivos[$i] == $fecha || $dia == 'dom') {

								$horas_festivos['fest_noc'] = $horas * $precios['festivo_hor'];
							}
						}
						return $horas_festivos;
					}
					$fecha = $row['date'];
					$domingo = $row['dia'];
					$salario_festivo = festivos($festivos, $fecha, $precios, $domingo, 8);

					$resumen = [
						'salario_basico' => 30284,
						'festivo' => (count($salario_festivo) == 0) ? 0 : $salario_festivo['fest_noc'],
						'H_E_N_Diur' => 0,
						'Nocturnas' => 0,
						'H_E_F_Diur' => 0,
						'H_E_F_Noc' => 0,
						'Rec_Nocturno' => 10596,
						'Aux_de_Trans' => 5400,

					];
					echo "</br>";
					print_r($resumen);
					$total = array_sum($resumen);
					echo "total : ", $total;


					$sql = "UPDATE attendance SET num_hr = '8' , salario_base= '" . $resumen['salario_basico'] . "' , festivo= '" . $resumen['festivo'] . "' , recargo_noc = '" . $resumen['Rec_Nocturno'] . "' ,  ";
					$sql .= " aux_tran = '" . $resumen['Aux_de_Trans'] . "' , hora_ext_diu = '" . $resumen['H_E_N_Diur'] . "'   , ";
					$sql .= " nocturnas = '" . $resumen['Nocturnas'] . "' , hora_ext_dom_diu = '" . $resumen['H_E_F_Diur'] . "'   , hora_ext_dom_noc = '" . $resumen['H_E_F_Noc'] . "' , total='$total'  WHERE id = '" . $row['uid'] . "'";
					$conn->query($sql);
				} else {
					$output['error'] = true;
					$output['message'] = $conn->error;
				}
			} else if ($query->num_rows < 1) {
				$output['error'] = true;
				$output['message'] = $salida1;
			}

			$sql = "SELECT *, attendance.id AS uid  FROM attendance LEFT JOIN employees ON employees.id=attendance.employee_id WHERE attendance.employee_id = '$id' AND  entradas=2 AND date = '$date_now'";
			$query = $conn->query($sql);
			$salida2 = $query->num_rows;
			$row = $query->fetch_assoc();

			if ($salida2 == 1 && $row['time_out'] == '00:00:00') {
				$sql = "UPDATE attendance SET time_out = '$time', salidas = 2 WHERE id = '" . $row['uid'] . "'  AND  entradas= 2";
				if ($conn->query($sql)) {
				// 	$output['message'] = 'Salida2: ' . $row['firstname'] . ' ' . $row['lastname'];

					$sql = "SELECT * FROM attendance WHERE id = '" . $row['uid'] . "'";
					$query = $conn->query($sql);
					$urow = $query->fetch_assoc();

					$time_in = $urow['time_in'];
					$time_out = $urow['time_out'];

					$sql = "SELECT * FROM employees LEFT JOIN schedules ON schedules.id=employees.schedule_id WHERE employees.id = '$id'";
					$query = $conn->query($sql);
					$srow = $query->fetch_assoc();

					$festivos = [
						'2021-05-17',
						'2021-06-07',
						'2021-06-14',
						'2021-07-05',
						'2021-07-20',
						'2021-08-07',
						'2021-08-16',
						'2021-10-18',
						'2021-11-01',
						'2021-11-15',
						'2021-12-08',
						'2021-12-25'
					];



					$salario_base = 1423500;
					$horas_trab = 50.64;
					$precio_hor =          round($salario_base / 240, 2);
					$dia_ord =             round(($salario_base / 240) * 8, 2);
					$festivo =             round($dia_ord * 0.75);
					$festivo_hor =         round($festivo / 8);
					$recargo_noc =         round($precio_hor * 0.35);
					$aux_trans = 3548;
					$hora_ext_diu =        ($salario_base * 1.25) / 240;
					$hora_ext_noc =        ($salario_base * 1.75) / 240;
					$hora_ext_dom_diu =    ($salario_base * 2) / 240;
					$hora_dom_noc =        ($salario_base * 2.1) / 240;
					$hora_ext_dom_noc =    ($salario_base * 2.5) / 240;
					$basico = $precio_hor * $horas_trab;
					$precios = [
						'salario_base' => $salario_base,
						'precio_hor' =>   $precio_hor,
						'precio_hor_noc' => $precio_hor + $recargo_noc,
						'dia_ord' =>      $dia_ord,
						'festivo' =>      $festivo,
						'festivo_hor' => $festivo_hor,
						'recargo_noc' =>  $recargo_noc,
						'aux_trans' =>    $aux_trans,
						'hora_ext_diu' =>       round($hora_ext_diu),
						'hora_ext_noc' =>       round($hora_ext_noc),
						'hora_ext_dom_diu' =>   round($hora_ext_dom_diu),
						'hora_dom_noc' =>       round($hora_dom_noc),
						'hora_ext_dom_noc' =>   round($hora_ext_dom_noc),
						'basico' => round($basico),
					];



					$jornada_ordinaria = [
						'entrada' => new DateTime('06:00:00'),
						'salida' => new DateTime('06:00:00'),
					];

					$jornada_nocturna = [
						'entrada' => new DateTime('22:00:00'),
						'salida' => new DateTime('23:59:00'),
					];



					$entrada = new DateTime($time_in);
					$salida = new DateTime($time_out);




					$inicio_noche   = new datetime('22:00:00');
					$fin_noche      = new datetime('06:00:00');
					$intermedia = new datetime('23:59:59');
					$medianoche     = new datetime('00:00:00');

					function nocturnas($entrada, $salida, $intermedia, $fin_noche, $medianoche)
					{



						if ($entrada >= $fin_noche &&  $entrada <= $intermedia) {
							return 0;
						} else if ($entrada >= $medianoche && $salida <= $fin_noche) {
							$calcular = $salida->diff($entrada);
							$horas = $calcular->format('%h');
							$minutos = $calcular->format('%i') / 60;
							$total  = number_format($horas + $minutos, 2);
							return $total;
						} else {
							return  0;
						}
					}

					$horas_noc = nocturnas($entrada, $salida, $intermedia, $fin_noche, $medianoche);
					echo $horas_noc;

					function salario($horas, $precios)
					{

						$calculo = $horas * $precios['precio_hor_noc'];
						$total = round($calculo, 2);
						return $total;
					}


					function recargo_nocturno($horas, $precios)
					{

						$calculo = $horas * $precios['recargo_noc'];
						$total = round($calculo, 2);
						return $total;
					}


					$recargo_nocturno = recargo_nocturno($horas_noc, $precios);



					function festivos($festivos, $fecha, $precios, $dia, $horas)
					{
						$horas_festivos = array();

						for ($i = 0; $i < count($festivos); $i++) {
							// echo $festivos[$i];
							if ($festivos[$i] == $fecha || $dia == 'dom') {

								$horas_festivos['fest_noc'] = $horas * $precios['festivo_hor'];
							}
						}
						return $horas_festivos;
					}
					$fecha = $row['date'];
					$domingo = $row['dia'];
					$salario_festivo = festivos($festivos, $fecha, $precios, $domingo, 8);

					$resumen = [
						'salario_basico' => 30284,
						'festivo' => (count($salario_festivo) == 0) ? 0 : $salario_festivo['fest_noc'],
						'H_E_N_Diur' => 0,
						'Nocturnas' => 0,
						'H_E_F_Diur' => 0,
						'H_E_F_Noc' => 0,
						'Rec_Nocturno' => 10596,
						'Aux_de_Trans' => 0,

					];
					echo "</br>";
					print_r($resumen);
					$total = array_sum($resumen);
					echo "total : ", $total;






					$sql = "UPDATE attendance SET num_hr = '8' , salario_base= '" . $resumen['salario_basico'] . "' , festivo= '" . $resumen['festivo'] . "' , recargo_noc = '" . $resumen['Rec_Nocturno'] . "' ,  ";
					$sql .= " aux_tran = '0' , hora_ext_diu = '" . $resumen['H_E_N_Diur'] . "'   , ";
					$sql .= " nocturnas = '" . $resumen['Nocturnas'] .  "' , hora_ext_dom_diu = '" . $resumen['H_E_F_Diur'] . "'   , hora_ext_dom_noc = '" . $resumen['H_E_F_Noc'] . "' , total='$total'  WHERE id = '" . $row['uid'] . "'";
					$conn->query($sql);
					$output['message'] = 'Salida: ' . $row['firstname'] . ' ' . $row['lastname'];
					
				} else {
					$output['error'] = true;
					$output['message'] = $conn->error;
				}
			}
		}
	} else {
		$output['error'] = true;
		$output['message'] = 'ID de empleado no encontrado';
	}
}

echo json_encode($output);
