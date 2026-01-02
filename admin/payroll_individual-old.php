<?php
include 'includes/session.php';

// Obtener el id del empleado a imprimir
$id = $_GET['id'];

// Obtener el rango de fechas
$range = $_POST['date_range'];
$ex = explode(' - ', $range);
$from = date('Y-m-d', strtotime($ex[0]));
$to = date('Y-m-d', strtotime($ex[1]));

// Como no usamos deducciones, asignamos 0
$deduction = 0;

// Para mostrar en el título del PDF
$from_title = date('M d, Y', strtotime($ex[0]));
$to_title = date('M d, Y', strtotime($ex[1]));

// Librería TCPDF
require_once('../tcpdf/tcpdf.php');
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Payslip: ' . $from_title . ' - ' . $to_title);
$pdf->SetHeaderData('', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetMargins(PDF_MARGIN_LEFT, '10', PDF_MARGIN_RIGHT);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();

$contents = '';

// Consulta para obtener la suma de los campos individuales SIN sumar "total" de la tabla
$sql = "SELECT 
          SUM(salario_base) AS salario_base,
          SUM(aux_tran) AS transporte,
          SUM(hora_ext_diu) AS ext_diu,
          SUM(hora_ext_diu) AS dom_diu,
          SUM(hora_ext_noc) AS dom_noc,
          attendance.employee_id AS empid,
          employees.employee_id AS employee,
          employees.firstname,
          employees.lastname,
          position.description AS position_name
        FROM attendance
        LEFT JOIN employees ON employees.id = attendance.employee_id
        LEFT JOIN position ON position.id = employees.position_id
        WHERE date BETWEEN '$from' AND '$to' 
          AND attendance.employee_id = '$id'
        GROUP BY attendance.employee_id 
        ORDER BY employees.lastname ASC, employees.firstname ASC";
$query = $conn->query($sql);

while ($row = $query->fetch_assoc()) {
    $empid = $row['empid'];

    // Cashadvance: se asume que si no hay registros, SUM(amount) retorna NULL, por lo que se asigna 0
    $casql = "SELECT SUM(amount) AS cashamount 
              FROM cashadvance 
              WHERE employee_id='$empid' 
                AND date_advance BETWEEN '$from' AND '$to'";
    $caquery = $conn->query($casql);
    $carow = $caquery->fetch_assoc();
    $cashadvance = $carow['cashamount'] ? $carow['cashamount'] : 0;

    // Descansos
    $sql1 = "SELECT SUM(pago) AS total_pagos 
             FROM descansos 
             WHERE employee_id='$empid' 
               AND fecha_desc BETWEEN '$from' AND '$to'";
    $query1 = $conn->query($sql1);
    $row1 = $query1->fetch_assoc();
    $pagos_descansos = $row1['total_pagos'] ? $row1['total_pagos'] : 0;

    // Otros
    $sql2 = "SELECT SUM(pago) AS total_otros, observacion 
             FROM otros 
             WHERE employee_id='$empid' 
               AND fecha_otro BETWEEN '$from' AND '$to'";
    $query2 = $conn->query($sql2);
    $row2 = $query2->fetch_assoc();
    $otros = $row2['total_otros'] ? $row2['total_otros'] : 0;
    $observacion = isset($row2['observacion']) ? $row2['observacion'] : '';

    // Incapacidad
    $sql3 = "SELECT SUM(pago) AS total_incapacidad 
             FROM incapacidad 
             WHERE employee_id='$empid' 
               AND create_at BETWEEN '$from' AND '$to'";
    $query3 = $conn->query($sql3);
    $row3 = $query3->fetch_assoc();
    $incapacidad = $row3['total_incapacidad'] ? $row3['total_incapacidad'] : 0;

    // Deducciones totales: solo cashadvance en este caso
    $total_deduction = $cashadvance;

    // Calcular el Subtotal
    $calc_subtotal = $row['salario_base']
                   + $row['ext_diu']
                   
                   + $row['dom_noc']
                   + $row['transporte'];

    // Calcular Salario Neto
    $suma = $calc_subtotal 
          + $pagos_descansos 
          + $otros 
          + $incapacidad 
          - $total_deduction;

    // Armado del HTML del reporte
    $contents .= '
        <h2 align="center">Nómina Guacamayas</h2>
        <h4 align="center">' . date('M d, Y', strtotime($from)) . " - " . date('M d, Y', strtotime($to)) . '</h4>
        <table cellspacing="0" cellpadding="3">  
            <tr>  
                <td width="25%" align="right">Nombre Empleado: </td>
                <td width="25%"><b>' . $row['firstname'] . " " . $row['lastname'] . '</b></td>
                <td width="25%" align="right">Salario Base: </td>
                <td width="25%" align="right">' . number_format($row['salario_base'], 2) . '</td>
            </tr>
           
            <tr> 
                <td></td> 
                <td></td>
                <td width="25%" align="right"><b>Aux. Transporte: </b></td>
                <td width="25%" align="right"><b>' . number_format($row['transporte'], 2) . '</b></td> 
            </tr>
            <tr> 
                <td></td> 
                <td></td>
                <td width="25%" align="right"><b>Días Trabajados: </b></td>
                <td width="25%" align="right"><b>' . number_format(($row['transporte'] / 3548), 2) . '</b></td> 
            </tr>
           
            <tr> 
                <td></td> 
                <td></td>
                <td width="25%" align="right"><b>Ext. Diurnas: </b></td>
                <td width="25%" align="right"><b>' . number_format($row['ext_diu'], 2) . '</b></td> 
            </tr>
            
           
            <tr> 
                <td></td> 
                <td></td>
                <td width="25%" align="right"><b>Ext. Nocturnas: </b></td>
                <td width="25%" align="right"><b>' . number_format($row['dom_noc'], 2) . '</b></td> 
            </tr>
            <tr> 
                <td></td> 
                <td></td>
                <td width="25%" align="right">Avance de Efectivo: </td>
                <td width="25%" align="right">' . number_format($cashadvance, 2) . '</td> 
            </tr>
            <tr> 
                <td></td> 
                <td></td>
                <td width="25%" align="right"><b>Total Deducciones:</b></td>
                <td width="25%" align="right"><b> -' . number_format($total_deduction, 2) . '</b></td> 
            </tr>
            <tr> 
                <td></td> 
                <td></td>
                <td width="25%" align="right"><b>Descansos:</b></td>
                <td width="25%" align="right"><b>' . number_format($pagos_descansos, 2) . '</b></td> 
            </tr>
            <tr> 
                <td></td> 
                <td></td>
                <td width="25%" align="right"><b>Otros:</b></td>
                <td width="25%" align="right"><b>' . number_format($otros, 2) . '</b></td> 
            </tr>
            <tr> 
                <td></td> 
                <td></td>
                <td width="25%" align="right"><b>Observación:</b></td>
                <td width="25%" align="right"><b>' . $observacion . '</b></td> 
            </tr>
            <tr> 
                <td></td> 
                <td></td>
                <td width="25%" align="right"><b>Incapacidad:</b></td>
                <td width="25%" align="right"><b>' . number_format($incapacidad, 2) . '</b></td> 
            </tr>
            <tr> 
                <td></td> 
                <td></td>
                <td width="25%" align="right"><b>Subtotal:</b></td>
                <td width="25%" align="right"><b>' . number_format($calc_subtotal, 2) . '</b></td> 
            </tr>
            <tr> 
                <td></td> 
                <td></td>
                <td width="25%" align="right"><b>Salario Neto:</b></td>
                <td width="25%" align="right"><b>' . number_format($suma, 2) . '</b></td> 
            </tr>
        </table>
        <br><hr>
    ';

    // Mostrar detalles de asistencia
    $asistencia = "SELECT * FROM attendance  
                   WHERE date BETWEEN '$from' AND '$to' 
                     AND employee_id = '$id'";
    $ejecute = $conn->query($asistencia);
    $horas = 0;

    while ($data = $ejecute->fetch_assoc()) {
        $horas += $data['num_hr'];
        $contents .= '<table border="1" cellpadding="2" cellspacing="5">
            <thead>
                <tr style="background-color:#FFFF00;color:#0000FF;">
                    <td width="100" align="center"><b>Fecha</b></td>
                    <td width="100" align="center"><b>Día</b></td>
                    <td width="100" align="center"><b>Entrada</b></td>
                    <td width="100" align="center"><b>Salida</b></td>
                    <td width="100" align="center"><b>Hrs</b></td>
                </tr>
            </thead>
            <tr>
                <td width="100" align="center"><b>' . $data['date'] . '</b></td>
                <td width="100" align="center"><b>' . $data['dia'] . '</b></td>
                <td width="100" align="center"><b>' . $data['time_in'] . '</b></td>
                <td width="100" align="center"><b>' . $data['time_out'] . '</b></td>
                <td width="100" align="center"><b>' . $data['num_hr'] . '</b></td>
            </tr>
        </table>';
    }

    $contents .= '<table>
                    <tr>
                        <td width="100" align="center"></td>
                        <td width="100" align="center"></td>
                        <td width="100" align="center">TOTAL HRS</td>
                        <td width="33%" align="right"><b>' . $horas . '</b></td>
                    </tr>
                  </table>';
}

// Mostrar el PDF
$pdf->writeHTML($contents);
$pdf->Output('payslip.pdf', 'I');
?>
