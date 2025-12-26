<?php

include 'includes/session.php';

// 17/05/2021 - Ascensión del Señor
// 07/06/2021 - Corphus Christi
// 14/06/2021 - Sagrado Corazón de Jesús
// 05/07/2021 - San Pedro y San Pablo
// 20/07/2021 - Día de la Independencia
// 07/08/2021 - Batalla de Boyacá
// 16/08/2021 - La Asunción de la Virgen
// 18/10/2021 - Día de la Raza
// 01/11/2021 - Todos los Santos
// 15/11/2021 - Independencia de Cartagena
// 08/12/2021 - Día de la Inmaculada Concepción
// 25/12/2021 - Día de Navidad

// pendiente colocar todos los domingos como festivos



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


$salario_base = 908526;
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
$hora_standar = new DateTime('22:00:00');
$hora1 = new DateTime('11:23:00');
$hora2 = new DateTime('22:50:00');
$hora_salida = new DateTime('23:00:00');

$diferencia = $hora1->diff($hora2);
$dia_prueba = '2021-11-08';
$diferencia_hors = $diferencia->format('%h');
$diferencia_min = $diferencia->format('%i');
$min_to_seg = round($diferencia_min / 60, 2);

$min_horas = number_format(($diferencia_hors + $min_to_seg) / 8, 2);
$a = number_format(($diferencia_hors + $min_to_seg), 2);
echo "numeros horas trabajadas : ", $a . "</br>";
($min_horas > 1) ? $min_horas = 1 : $min_horas;
// echo $min_horas;

// dia ordinario
if ($min_horas <= 1) {
  $sb = round($min_horas  * $precios['dia_ord'], 0);
}
// fin dia ordinario




// hora ext diurna
$hora_add_diur = 0;
if ($a > 8 && $hora2 <= $hora_standar) {
  $hora_diur = ($diferencia_hors + $min_to_seg) - 8;

  $hora_add_diur = $hora_diur * 4732;
  // echo $hora_add_diur;
}
// fin hora ext diurna


// hora_ext_diur_festivo
$hora_ext_diur_festivo = 0;
for ($i = 0; $i < count($festivos); $i++) {
  $dias_festivos = $festivos[$i];
  if ($a > 8 && $hora2 <= $hora_standar && $dia_prueba == $dias_festivos) {
    $hora_diur = ($diferencia_hors + $min_to_seg) - 8;
    echo "horas extras diur festivos : ", $hora_diur;
    $hora_ext_diur_festivo = $hora_diur * $precios['hora_ext_dom_diu'];
    $hora_add_diur = 0;
    // echo $hora_add_diur;
  }
}

// FIN hora_ext_diur_festivo



// dias trabajados 
$fecha1 = new DateTime('now');
$fecha2 = new DateTime('2021-05-3');
$calc_dias = $fecha1->diff($fecha2);
$dias = $calc_dias->format('%d');
// $aux_t = $dias * 3548;
$aux_t = 1 * 3548;
$calculo_total = 0;
$hora_extra_noc = 0;


// recargo nocturno 
if ($hora2 > $hora_standar) {
  $standar_hora2 = $hora2->diff($hora_standar);
  $standar_hora = $standar_hora2->format('%h');
  $standar_min = $standar_hora2->format('%i');

  if ($standar_hora == 1 && $standar_hora != 0) {



    $calculo_total = round($standar_hora * 1325, 2);

    // echo $calculo_total;
  } else {
    $calculo_min = round($standar_min / 60, 2);
    $calculo_total = round($calculo_min * 1325, 2);
  }
}
// fin recargo noecturno 




// hora ext nocturna
if ($hora2 > $hora_salida) {

  $diferencia_salida = $hora2->diff($hora_salida);
  $hora_noc_horas = $diferencia_salida->format('%h');
  $hora_noc_min = $diferencia_salida->format('%i');
  $hora_extra_noc = round($hora_noc_min / 60, 2);
  
  $hora_extra_noc = round(($hora_extra_noc + $hora_noc_horas) * 6624, 0);
}
// fin hora ext nocturna 


// $hora2 = new DateTime('22:11:00');
// $hora_salida = new DateTime('23:00:00');

// calculo dia festivo
$precio_festivo = 0;
for ($i = 0; $i < count($festivos); $i++) {
  $dias_festivos = $festivos[$i];
  if ($min_horas <= 1 && $dia_prueba == $dias_festivos) {
    echo $precio_festivo = $min_horas * $precios['festivo'];
  }
}
// fin festivos

// calculo hora extra festivo 
$hora_extra_festivo = 0;
for ($i = 0; $i < count($festivos); $i++) {
  $dias_festivos = $festivos[$i];
  if ($hora2 > $hora_salida && $dia_prueba == $dias_festivos) {

    $diferencia_salida = $hora2->diff($hora_salida);
    $hora_noc_horas = $diferencia_salida->format('%h');
    $hora_noc_min = $diferencia_salida->format('%i');
    $hora_extra_noc = round($hora_noc_min / 60, 2);

    $hora_extra_festivo = round(($hora_extra_noc + $hora_noc_horas) * $precios['hora_ext_dom_noc']);
    $hora_extra_noc = 0;
  }
}
// fin calculo hora extra festivo 







$subtotal = $sb + $calculo_total + $aux_t + $hora_extra_noc + $hora_add_diur + $precio_festivo + $hora_ext_diur_festivo;


echo "datos ";
$datos = [
  "salario_base" => $sb,
  "festivo" => $precio_festivo,
  "recargo_noc" => $calculo_total,
  "aux_transporte" => $aux_t,
  "hora_ext_diur" => $hora_add_diur,
  "hora_ext_noc" => $hora_extra_noc,
  "hora_ext_diur_fest" => $hora_ext_diur_festivo,
  "hora_ext_noc_fest" => $hora_extra_festivo,
  "subtotal" => $subtotal,
];
print_r($datos);
// pendiente sacar el rango para una hora extra diurna. 
// pendiente sacar el calculo del dia festivo.
// pendiente calcular hora extra nocturna festiva.