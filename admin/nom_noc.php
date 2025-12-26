
<?php
$timezone = 'America/Bogota';
date_default_timezone_set($timezone);

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

$date = new DateTime('now');
echo $date->format('h:i:s');


$jornada_ordinaria = [
    'entrada' => new DateTime('22:00:00'),
    'salida' => new DateTime('00:00:00'),
];

$jornada_nocturna = [
    'entrada' => new DateTime('00:00:00'),
    'salida' => new DateTime('06:00:00'),
];



$entrada = new DateTime('22:00:00');
$salida = new DateTime('23:59:00');



$inicio_noche   = new datetime('22:00:00');
$fin_noche      = new datetime('06:00:00');
$intermedia = new datetime('23:59:59');
$medianoche     = new datetime('00:00:00');

function nocturnas ($entrada,$salida,$intermedia,$fin_noche,$medianoche){



if ($entrada >=$fin_noche &&  $entrada <= $intermedia) {
    return 0;
}else if ($entrada >= $medianoche && $salida <= $fin_noche){
    $calcular = $salida->diff($entrada);
    $horas = $calcular->format('%h');
    $minutos = $calcular->format('%i') / 60;
    $total  = number_format($horas + $minutos,2);
    return $total;
}else{
return  0;
}
}

$horas_noc = nocturnas($entrada,$salida,$intermedia,$fin_noche,$medianoche);
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
$fecha = '2021-07-14';
$dia = 'sab';
$salario_festivo = festivos($festivos, $fecha, $precios, $dia, $horas_noc);

$resumen = [
    'salario_basico' => round(salario($horas_noc, $precios), 0),
    'festivo' => (count($salario_festivo) == 0) ? 0 : $salario_festivo['fest_noc'],
    'H.E.N Diur' => 0,
    'H.E.N Noc' => 0,
    'H.E.F Diur' => 0,
    'H.E.F Noc' => 0,
    'Rec. Nocturno' => $recargo_nocturno,
    'Aux. de Trans' => 3548,

];
echo "</br>";
print_r($resumen);
$total = array_sum($resumen);
echo "total : ", $total;


