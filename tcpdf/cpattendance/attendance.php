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
				$sql = "INSERT INTO attendance (employee_id, date, dia, time_in, status, entradas) VALUES ('$id', '$date_now', '$dia', NOW(), '$logstatus', $entrada)";
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

				$sql = "INSERT INTO attendance (employee_id, date, dia, time_in, status, entradas) VALUES ('$id', '$date_now', '$dia', NOW(), '$logstatus', $entrada)";
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
				$sql = "UPDATE attendance SET time_out = NOW(), salidas = 1 WHERE id = '" . $row['uid'] . "'  AND  entradas= 1";
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
					echo "<pre>";
					print_r($festivos);


					$salario_base = $row['salario'];
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

					echo "<pre>";
					print_r($precios);

					$jornada_ordinaria = [
						'entrada' => new DateTime('06:00:00'),
						'salida' => new DateTime('22:00:00'),
					];

					$jornada_nocturna = [
						'entrada' => new DateTime('22:00:00'),
						'salida' => new DateTime('06:00:00'),
					];



					$entrada = new DateTime($time_in);
					$salida = new DateTime($time_out);




					if ($salida < $jornada_ordinaria['salida']) {
						echo 'true';
					} else {
						echo 'false';
					}


					function diurna($entrada, $salida, $jornada_ordinaria)
					{

						if ($entrada > $jornada_ordinaria['salida']) {
							return 0;
						} else if ($salida < $jornada_ordinaria['salida']) {
							$fin_inicio = $salida->diff($entrada);
							$horas = $fin_inicio->format('%h');
							$total = round($horas + ($fin_inicio->format('%i') / 60), 2);
							return $total;
						} else {
							$cambio_hora = $jornada_ordinaria['salida']->diff($entrada);
							$h = $cambio_hora->format('%h');
							$t =  round($h + ($cambio_hora->format('%i') / 60), 2);
							return $t;
						}
					}

					$respuesta = diurna($entrada, $salida, $jornada_ordinaria);
					echo "horas diurnas : ", $respuesta . "</br>";





					function extrasDiurnas($horas)
					{

						if ($horas > 8) {
							$calculo = $horas - 8;
							return $calculo;
						} else {
							return 0;
						}
					}

					$extas_diurnas = extrasDiurnas($respuesta) * $precios['hora_ext_diu'];


					echo "horas extas diurnas : ", extrasDiurnas($respuesta) . "</br>";


					// $entrada = new DateTime('00:00:00');
					// $salida = new DateTime('03:21:00');


					function nocturna($salida, $entrada, $jornada_ordinaria)
					{

						if ($salida < $jornada_ordinaria['salida']) {
							return 0;
						} else if ($entrada > $jornada_ordinaria['salida']) {
							$hora_fin_inicio = $salida->diff($entrada);
							$horas = $hora_fin_inicio->format('%h');
							$total = round($horas + ($hora_fin_inicio->format('%i') / 60), 2);
							return $total;
						} else {
							$hora_final_ordinaria = $salida->diff($jornada_ordinaria['salida']);
							$h = $hora_final_ordinaria->format('%h');
							$t =  round($h + ($hora_final_ordinaria->format('%i') / 60), 2);
							return $t;
						}
					}

					$nocturnas = nocturna($salida, $entrada, $jornada_ordinaria);

					echo "nocturnas : ", $nocturnas . "</br>";

					function extrasNocturnas($horas, $nocturnas)
					{

						if ($horas > 8 && $nocturnas != 0) {
							return $nocturnas;
						} else {
							return 0;
						}
					}


					$extas_nocturnas = extrasNocturnas($respuesta, $nocturnas) * $precios['hora_ext_noc'];

					echo "horas extras nocturnas : ", extrasNocturnas($respuesta, $nocturnas);


					function salario($nocturnas, $respuesta, $precio_hor_noc, $dia_ord, $precio_hor)
					{

						if ($nocturnas != 0 && $respuesta == 0) {
							$nocturnas = $nocturnas * $precio_hor_noc;
							return $nocturnas;
						} else if ($respuesta != 0 && $respuesta > 8) {

							$respuesta = 1 * $dia_ord;
							return $respuesta;
						} else {
							$respuesta = $respuesta * $precio_hor;
							return $respuesta;
						}
					}

					function festivos($festivos, $fecha, $extas_diurnas, $extas_nocturnas, $salario, $dia, $precio_hor, $festivo_hor, $festivo, $hora_ext_dom_diu, $hora_ext_dom_noc)
					{
						$horas_festivos = array();
						$cal_horas = round($salario / $precio_hor, 2);

						$salario_festivo = ($cal_horas < 8) ? $cal_horas * $festivo_hor : $festivo;
						for ($i = 0; $i < count($festivos); $i++) {
							// echo $festivos[$i];
							if ($festivos[$i] == $fecha || $dia == 'dom') {


								$horas_festivos['fest_diur'] = $extas_diurnas * $hora_ext_dom_diu;
								$horas_festivos['fest_noc'] = $extas_nocturnas * $hora_ext_dom_noc;
								$horas_festivos['festivo'] = $salario_festivo;
							}
						}
						return $horas_festivos;
					}



					$fecha = $row['date'];
					$domingo = $row['dia'];
					$salario_festivo = festivos($festivos, $fecha, extrasDiurnas($respuesta), extrasNocturnas($respuesta, $nocturnas), salario($nocturnas, $respuesta, $precios['precio_hor_noc'], $precios['dia_ord'], $precios['precio_hor']), $domingo, $precios['precio_hor'], $precios['festivo_hor'], $precios['festivo'], $precios['hora_ext_dom_diu'], $precios['hora_ext_dom_noc']);


					$resumen = [
						'salario_basico' => round(salario($nocturnas, $respuesta, $precios['precio_hor_noc'], $precios['dia_ord'], $precios['precio_hor']), 0),
						'festivo' => (count($salario_festivo) == 0) ? 0 : $salario_festivo['festivo'],
						'H_E_N_Diur' => (count($salario_festivo) == 0) ? $extas_diurnas : 0,
						'H_E_N_Noc' => (count($salario_festivo) == 0) ? $extas_nocturnas : 0,
						'H_E_F_Diur' => (count($salario_festivo) == 0) ? 0 : $salario_festivo['fest_diur'],
						'H_E_F_Noc' => (count($salario_festivo) == 0) ? 0 : $salario_festivo['fest_noc'],
						'Rec_Nocturno' => $nocturnas * $precios['recargo_noc'],
						'Aux_de_Trans' => 3548,

					];
					echo "</br>";
					print_r($resumen);
					$total = array_sum($resumen);
					echo "total : ", $total;

					$sql = "UPDATE attendance SET num_hr = '1' , salario_base= '" . $resumen['salario_basico'] . "' , festivo= '" . $resumen['festivo'] . "' , recargo_noc = '" . $resumen['Rec_Nocturno'] . "' ,  ";
					$sql .= " aux_tran = '" . $resumen['Aux_de_Trans'] . "' , hora_ext_diu = '" . $resumen['H_E_N_Diur'] . "'   , ";
					$sql .= " hora_ext_noc = '" . $resumen['H_E_N_Noc'] . "' , hora_ext_dom_diu = '" . $resumen['H_E_F_Diur'] . "'   , hora_ext_dom_noc = '" . $resumen['H_E_F_Noc'] . "' , total='$total'  WHERE id = '" . $row['uid'] . "'";
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
				$sql = "UPDATE attendance SET time_out = NOW(), salidas = 2 WHERE id = '" . $row['uid'] . "'  AND  entradas= 2";
				if ($conn->query($sql)) {
					$output['message'] = 'Salida2: ' . $row['firstname'] . ' ' . $row['lastname'];

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
					echo "<pre>";
					print_r($festivos);


					$salario_base = $row['salario'];
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

					echo "<pre>";
					print_r($precios);

					$jornada_ordinaria = [
						'entrada' => new DateTime('06:00:00'),
						'salida' => new DateTime('22:00:00'),
					];

					$jornada_nocturna = [
						'entrada' => new DateTime('22:00:00'),
						'salida' => new DateTime('06:00:00'),
					];



					$entrada = new DateTime($time_in);
					$salida = new DateTime($time_out);




					if ($salida < $jornada_ordinaria['salida']) {
						echo 'true';
					} else {
						echo 'false';
					}


					function diurna($entrada, $salida, $jornada_ordinaria)
					{

						if ($entrada > $jornada_ordinaria['salida']) {
							return 0;
						} else if ($salida < $jornada_ordinaria['salida']) {
							$fin_inicio = $salida->diff($entrada);
							$horas = $fin_inicio->format('%h');
							$total = round($horas + ($fin_inicio->format('%i') / 60), 2);
							return $total;
						} else {
							$cambio_hora = $jornada_ordinaria['salida']->diff($entrada);
							$h = $cambio_hora->format('%h');
							$t =  round($h + ($cambio_hora->format('%i') / 60), 2);
							return $t;
						}
					}

					$respuesta = diurna($entrada, $salida, $jornada_ordinaria);
					echo "horas diurnas : ", $respuesta . "</br>";





					function extrasDiurnas($horas)
					{

						if ($horas > 8) {
							$calculo = $horas - 8;
							return $calculo;
						} else {
							return 0;
						}
					}

					$extas_diurnas = extrasDiurnas($respuesta) * $precios['hora_ext_diu'];


					echo "horas extas diurnas : ", extrasDiurnas($respuesta) . "</br>";


					// $entrada = new DateTime('00:00:00');
					// $salida = new DateTime('03:21:00');


					function nocturna($salida, $entrada, $jornada_ordinaria)
					{

						if ($salida < $jornada_ordinaria['salida']) {
							return 0;
						} else if ($entrada > $jornada_ordinaria['salida']) {
							$hora_fin_inicio = $salida->diff($entrada);
							$horas = $hora_fin_inicio->format('%h');
							$total = round($horas + ($hora_fin_inicio->format('%i') / 60), 2);
							return $total;
						} else {
							$hora_final_ordinaria = $salida->diff($jornada_ordinaria['salida']);
							$h = $hora_final_ordinaria->format('%h');
							$t =  round($h + ($hora_final_ordinaria->format('%i') / 60), 2);
							return $t;
						}
					}

					$nocturnas = nocturna($salida, $entrada, $jornada_ordinaria);

					echo "nocturnas : ", $nocturnas . "</br>";

					function extrasNocturnas($horas, $nocturnas)
					{

						if ($horas > 8 && $nocturnas != 0) {
							return $nocturnas;
						} else {
							return 0;
						}
					}


					$extas_nocturnas = extrasNocturnas($respuesta, $nocturnas) * $precios['hora_ext_noc'];

					echo "horas extras nocturnas : ", extrasNocturnas($respuesta, $nocturnas);


					function salario($nocturnas, $respuesta, $precio_hor_noc, $dia_ord, $precio_hor)
					{

						if ($nocturnas != 0 && $respuesta == 0) {
							$nocturnas = $nocturnas * $precio_hor_noc;
							return $nocturnas;
						} else if ($respuesta != 0 && $respuesta > 8) {

							$respuesta = 1 * $dia_ord;
							return $respuesta;
						} else {
							$respuesta = $respuesta * $precio_hor;
							return $respuesta;
						}
					}

					function festivos($festivos, $fecha, $extas_diurnas, $extas_nocturnas, $salario, $dia, $precio_hor, $festivo_hor, $festivo, $hora_ext_dom_diu, $hora_ext_dom_noc)
					{
						$horas_festivos = array();
						$cal_horas = round($salario / $precio_hor, 2);

						$salario_festivo = ($cal_horas < 8) ? $cal_horas * $festivo_hor : $festivo;
						for ($i = 0; $i < count($festivos); $i++) {
							// echo $festivos[$i];
							if ($festivos[$i] == $fecha || $dia == 'dom') {


								$horas_festivos['fest_diur'] = $extas_diurnas * $hora_ext_dom_diu;
								$horas_festivos['fest_noc'] = $extas_nocturnas * $hora_ext_dom_noc;
								$horas_festivos['festivo'] = $salario_festivo;
							}
						}
						return $horas_festivos;
					}



					$fecha = $row['date'];
					$domingo = $row['dia'];
					$salario_festivo = festivos($festivos, $fecha, extrasDiurnas($respuesta), extrasNocturnas($respuesta, $nocturnas), salario($nocturnas, $respuesta, $precios['precio_hor_noc'], $precios['dia_ord'], $precios['precio_hor']), $domingo, $precios['precio_hor'], $precios['festivo_hor'], $precios['festivo'], $precios['hora_ext_dom_diu'], $precios['hora_ext_dom_noc']);


					$resumen = [
						'salario_basico' => round(salario($nocturnas, $respuesta, $precios['precio_hor_noc'], $precios['dia_ord'], $precios['precio_hor']), 0),
						'festivo' => (count($salario_festivo) == 0) ? 0 : $salario_festivo['festivo'],
						'H_E_N_Diur' => (count($salario_festivo) == 0) ? $extas_diurnas : 0,
						'H_E_N_Noc' => (count($salario_festivo) == 0) ? $extas_nocturnas : 0,
						'H_E_F_Diur' => (count($salario_festivo) == 0) ? 0 : $salario_festivo['fest_diur'],
						'H_E_F_Noc' => (count($salario_festivo) == 0) ? 0 : $salario_festivo['fest_noc'],
						'Rec_Nocturno' => $nocturnas * $precios['recargo_noc'],
						'Aux_de_Trans' => 3548,

					];
					echo "</br>";
					print_r($resumen);
					$total = array_sum($resumen);
					echo "total : ", $total;

					$sql = "UPDATE attendance SET num_hr = '4' , salario_base= '" . $resumen['salario_basico'] . "' , festivo= '" . $resumen['festivo'] . "' , recargo_noc = '" . $resumen['Rec_Nocturno'] . "' ,  ";
					$sql .= " aux_tran = '0' , hora_ext_diu = '" . $resumen['H_E_N_Diur'] . "'   , ";
					$sql .= " hora_ext_noc = '" . $resumen['H_E_N_Noc'] . "' , hora_ext_dom_diu = '" . $resumen['H_E_F_Diur'] . "'   , hora_ext_dom_noc = '" . $resumen['H_E_F_Noc'] . "' , total='$total'  WHERE id = '" . $row['uid'] . "'";
					$conn->query($sql);
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
