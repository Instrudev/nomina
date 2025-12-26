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
echo "
<pre>";
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



$entrada = new DateTime('11:00:00');
$salida = new DateTime('22:31:00');


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

$extas_diurnas = extrasDiurnas($respuesta) * 4732;

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


$extas_nocturnas = extrasNocturnas($respuesta, $nocturnas) * 6625;
echo "horas extras nocturnas : ", extrasNocturnas($respuesta, $nocturnas);


function salario($nocturnas, $respuesta)
{

    if ($nocturnas != 0 && $respuesta == 0) {
        $nocturnas = $nocturnas * 5110.53;
        return $nocturnas;
    } else if ($respuesta != 0 && $respuesta > 8) {

        $respuesta = 1 * 30284.2;
        return $respuesta;
    } else {
        $respuesta = $respuesta * 3785.53;
        return $respuesta;
    }
}

function festivos($festivos, $fecha, $extas_diurnas, $extas_nocturnas, $salario)
{
    $horas_festivos = array();
    $cal_horas = round($salario / 3785.53, 2);

    $salario_festivo = ($cal_horas < 8) ? $cal_horas * 2839 : 22713;
    for ($i = 0; $i < count($festivos); $i++) {
        // echo $festivos[$i];
        if ($festivos[$i] == $fecha ) {


            $horas_festivos['fest_diur'] = $extas_diurnas * 7571;
            $horas_festivos['fest_noc'] = $extas_nocturnas * 9464;
            $horas_festivos['festivo'] = $salario_festivo;
        }
    }
    return $horas_festivos;
}



$fecha = '2021-06-14';
$salario_festivo = festivos($festivos, $fecha, extrasDiurnas($respuesta), extrasNocturnas($respuesta, $nocturnas), salario($nocturnas, $respuesta));


$resumen = [
    'salario_basico' => round(salario($nocturnas, $respuesta), 0),
    'festivo' => (count($salario_festivo) == 0) ? 0 : $salario_festivo['festivo'],
    'H.E.N Diur' => (count($salario_festivo) == 0) ? $extas_diurnas : 0,
    'H.E.N Noc' => (count($salario_festivo) == 0) ? $extas_nocturnas : 0,
    'H.E.F Diur' => (count($salario_festivo) == 0) ? 0 : $salario_festivo['fest_diur'],
    'H.E.F Noc' => (count($salario_festivo) == 0) ? 0 : $salario_festivo['fest_noc'],
    'Rec. Nocturno' => $nocturnas * 1325,
    'Aux. de Trans' => 3548,

];
echo "</br>";
print_r($resumen);
$total = array_sum($resumen);
echo "total : ", $total;