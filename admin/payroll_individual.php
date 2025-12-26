<?php
include 'includes/session.php';

// Parámetros de entrada
$id    = $_GET['id'];
$range = $_POST['date_range'];
$ex    = explode(' - ', $range);
$from  = date('Y-m-d', strtotime($ex[0]));
$to    = date('Y-m-d', strtotime($ex[1]));
$from_title = date('M d, Y', strtotime($ex[0]));
$to_title   = date('M d, Y', strtotime($ex[1]));

// Carga de Dompdf
require_once('../dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$dompdf = new Dompdf();
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nómina Guacamayas</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@600&family=Open+Sans&display=swap');

    body {
      margin: 0; padding: 20px;
      background: #F9FAFB;
      color: #374151;
      font-family: 'Open Sans', sans-serif;
    }
    .header {
      text-align: center;
      margin-bottom: 20px;
    }
    .header h2 {
      margin: 0;
      font-family: 'Montserrat', sans-serif;
      font-size: 26px;
      color: #111827;
    }
    .header .dates {
      margin-top: 4px;
      font-size: 14px;
      color: #6B7280;
    }

    /* Resumen en tabla de dos columnas */
    .summary-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 25px;
    }
    .summary-table td {
      width: 50%;
      vertical-align: top;
      padding: 6px;
    }
    .card {
      background: #FFFFFF;
      border: 1px solid #2563EB;
      border-radius: 6px;
      padding: 10px;
    }
    .card .label {
      font-size: 10px;
      color: #6B7280;
      text-transform: uppercase;
      margin-bottom: 4px;
    }
    .card .value {
      font-family: 'Montserrat', sans-serif;
      font-size: 16px;
      color: #2563EB;
      font-weight: bold;
    }

    /* Tabla de asistencias */
    table.attendance {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
      margin-top: 20px;
    }
    table.attendance thead th {
      background: #2563EB;
      color: #FFFFFF;
      padding: 8px;
      text-align: left;
      font-family: 'Montserrat', sans-serif;
      text-transform: uppercase;
    }
    table.attendance tbody td {
      background: #FFFFFF;
      padding: 8px;
      border-bottom: 1px solid #E5E7EB;
    }
    table.attendance tbody tr:nth-child(even) td {
      background: #F3F4F6;
    }
    .hrs {
      text-align: right;
      color: #2563EB;
      font-weight: 600;
    }
    .total-row td {
      padding-top: 12px;
      border-top: 2px solid #2563EB;
      font-weight: 600;
    }
    .total-label {
      text-align: left;
      color: #111827;
    }
    .total-value {
      text-align: right;
      color: #2563EB;
    }
  </style>
</head>
<body>

  <div class="header">
    <h2>Nómina Guacamayas</h2>
    <div class="dates"><?= "{$from_title} – {$to_title}" ?></div>
  </div>

  <?php
  // Consulta principal
  $sql = "
    SELECT 
      SUM(salario_base)      AS salario_base,
      SUM(aux_tran)          AS transporte,
      SUM(hora_ext_diu)      AS ext_diu,
      SUM(hora_ext_noc)      AS dom_noc,
      attendance.employee_id AS empid,
      employees.firstname,
      employees.lastname
    FROM attendance
    LEFT JOIN employees ON employees.id = attendance.employee_id
    WHERE date BETWEEN '$from' AND '$to'
      AND attendance.employee_id = '$id'
    GROUP BY attendance.employee_id
  ";
  $query = $conn->query($sql);
  if ($row = $query->fetch_assoc()):
    // Cálculos extra
    $empid           = $row['empid'];
    $cashadvance     = $conn->query("SELECT COALESCE(SUM(amount),0) AS c FROM cashadvance WHERE employee_id='$empid' AND date_advance BETWEEN '$from' AND '$to'")->fetch_assoc()['c'];
    $pagos_descansos = $conn->query("SELECT COALESCE(SUM(pago),0)   AS p FROM descansos    WHERE employee_id='$empid' AND fecha_desc   BETWEEN '$from' AND '$to'")->fetch_assoc()['p'];
    $row2            = $conn->query("SELECT COALESCE(SUM(pago),0) AS o, observacion FROM otros WHERE employee_id='$empid' AND fecha_otro BETWEEN '$from' AND '$to'")->fetch_assoc();
    $otros           = $row2['o'];
    $observacion     = $row2['observacion'] ?: '–';
    $incapacidad     = $conn->query("SELECT COALESCE(SUM(pago),0) AS i FROM incapacidad WHERE employee_id='$empid' AND create_at BETWEEN '$from' AND '$to'")->fetch_assoc()['i'];
    $total_deduction = $cashadvance;
    $calc_subtotal   = $row['salario_base'] + $row['ext_diu'] + $row['dom_noc'] + $row['transporte'];
    $suma            = $calc_subtotal + $pagos_descansos + $otros + $incapacidad - $total_deduction;

    // Preparar items de resumen
    $items = [
      ['Empleado',         htmlspecialchars($row['firstname'].' '.$row['lastname'])],
      ['Salario Base',     number_format($row['salario_base'],2)],
      ['Aux. Transporte',  number_format($row['transporte'],2)],
      ['Días Trabajados',  number_format($row['transporte']/3548,2)],
      ['Ext. Diurnas',     number_format($row['ext_diu'],2)],
      ['Ext. Nocturnas',   number_format($row['dom_noc'],2)],
      ['Avance Efectivo',  number_format($cashadvance,2)],
      ['Deducciones',      '-'.number_format($total_deduction,2)],
      ['Descansos',        number_format($pagos_descansos,2)],
      ['Otros',            number_format($otros,2)],
      ['Incapacidad',      number_format($incapacidad,2)],
      ['Subtotal',         number_format($calc_subtotal,2)],
      ['Salario Neto',     number_format($suma,2)],
    ];
  ?>
    <table class="summary-table">
      <?php for ($i = 0; $i < count($items); $i += 2): 
        $left  = $items[$i];
        $right = isset($items[$i+1]) ? $items[$i+1] : ['PDF','COMPROBANTE DE PAGO'];
      ?>
      <tr>
        <td>
          <div class="card">
            <div class="label"><?= $left[0] ?></div>
            <div class="value"><?= $left[1] ?></div>
          </div>
        </td>
        <td>
          <div class="card">
            <div class="label"><?= $right[0] ?></div>
            <div class="value"><?= $right[1] ?></div>
          </div>
        </td>
      </tr>
      <?php endfor; ?>
    </table>

    <table class="attendance">
      <thead>
        <tr>
          <th>Fecha</th><th>Día</th><th>Entrada</th><th>Salida</th><th>Hrs</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $horas = 0;
        $res   = $conn->query("SELECT * FROM attendance WHERE date BETWEEN '$from' AND '$to' AND employee_id='$id'");
        while ($d = $res->fetch_assoc()):
          $horas += $d['num_hr'];
        ?>
        <tr>
          <td><?= $d['date']    ?></td>
          <td><?= $d['dia']     ?></td>
          <td><?= $d['time_in'] ?></td>
          <td><?= $d['time_out']?></td>
          <td class="hrs"><?= number_format($d['num_hr'],2) ?></td>
        </tr>
        <?php endwhile; ?>
        <tr class="total-row">
          <td colspan="4" class="total-label">TOTAL HRS</td>
          <td class="total-value"><?= number_format($horas,2) ?></td>
        </tr>
      </tbody>
    </table>
  <?php endif; ?>

</body>
</html>
<?php
$html = ob_get_clean();

// Renderizar PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream('payslip.pdf', ['Attachment'=>0]);
?>
