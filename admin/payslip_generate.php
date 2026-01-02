<?php
include 'includes/session.php';

$range = $_POST['date_range'];
$ex = explode(' - ', $range);
$from = date('Y-m-d', strtotime($ex[0]));
$to = date('Y-m-d', strtotime($ex[1]));


$sql = "SELECT *, SUM(amount) as total_amount FROM deductions";
$query = $conn->query($sql);
$drow = $query->fetch_assoc();
$deduction = $drow['total_amount'];

$from_title = date('M d, Y', strtotime($ex[0]));
$to_title = date('M d, Y', strtotime($ex[1]));

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

// $sql = "SELECT *, SUM(num_hr) AS total_hr, attendance.employee_id AS empid, employees.employee_id AS employee FROM attendance LEFT JOIN employees ON employees.id=attendance.employee_id LEFT JOIN position ON position.id=employees.position_id WHERE date BETWEEN '$from' AND '$to' GROUP BY attendance.employee_id ORDER BY employees.lastname ASC, employees.firstname ASC";

$sql = "SELECT *, SUM(salario_base) as salario_base , SUM(festivo) as fest ,SUM(aux_tran) as transporte , SUM(recargo_noc) as recargo , SUM(hora_ext_diu) as ext_diu, SUM(hora_ext_noc) as ext_noc, SUM(hora_ext_dom_diu) as dom_diu , sum(hora_ext_dom_noc) as dom_noc , SUM(total) as neto, attendance.employee_id AS empid, employees.employee_id AS employee FROM attendance LEFT JOIN employees ON employees.id=attendance.employee_id LEFT JOIN position ON position.id=employees.position_id WHERE date BETWEEN '$from' AND '$to' GROUP BY attendance.employee_id ORDER BY employees.lastname ASC, employees.firstname ASC";

//
$query = $conn->query($sql);
$total_pagar = 0;
while ($row = $query->fetch_assoc()) {
	$empid = $row['empid'];

	$casql = "SELECT *, SUM(amount) AS cashamount FROM cashadvance WHERE employee_id='$empid' AND date_advance BETWEEN '$from' AND '$to'";
	$caquery = $conn->query($casql);
	$carow = $caquery->fetch_assoc();
	$cashadvance = $carow['cashamount'];

	$sql1 = "SELECT *, SUM(pago) AS total_pagos FROM descansos WHERE employee_id='$empid' AND fecha_desc BETWEEN '$from' AND '$to'";
	$query1 = $conn->query($sql1);
	$row1 = $query1->fetch_assoc();
	$pagos_descansos = $row1['total_pagos'];
	
	$sql2 = "SELECT *, SUM(pago) AS total_otros FROM otros WHERE employee_id='$empid' AND fecha_otro BETWEEN '$from' AND '$to'";
	$query2 = $conn->query($sql2);
	$row2 = $query2->fetch_assoc();
	
	$sql3 = "SELECT *, SUM(pago) AS total_incapacidad FROM incapacidad WHERE employee_id='$empid' AND create_at BETWEEN '$from' AND '$to'";
    $query3 = $conn->query($sql3);
	$row3 = $query3->fetch_assoc();
	// otros
	
	$otros = $row2['total_otros'];
	$observacion = $row2['observacion'];
	$incapacidad = $row3['total_incapacidad'];
	// fin otros
	$pagos_descansos = $row1['total_pagos'];

	$total_deduction = $deduction + $cashadvance+ $otros;
	$suma = $row['neto'] + $pagos_descansos + $incapacidad  - $total_deduction;
	$subtotal = $row['neto'];
	 

	$total_deduction = $deduction + $cashadvance;


	$porcentaje = ($row['salario']) * 0.08;
	$salud_pension =  $porcentaje;
	$total_pagar = $suma - $salud_pension;
	$contents .= '
			<h2 align="center">Nomina Guacamayas</h2>
			<h4 align="center">' . $from_title . " - " . $to_title . '</h4>
			<table cellspacing="0" cellpadding="3">  
    	       	<tr>  
            		<td width="25%" align="right">Nombre Empleado: </td>
                 	<td width="25%"><b>' . $row['firstname'] . " " . $row['lastname'] . '</b></td>
				 	<td width="25%" align="right">Diurnas: </td>
                 	<td width="25%" align="right">' . number_format($row['salario_base'], 2) . '</td>
    	    	</tr>
    	    	<tr>
    	    		<td width="25%" align="right">ID Empleado: </td>
				 	<td width="25%">' . $row['employee'] . '</td>   
				 	<td width="25%" align="right">Festivos: </td>
				 	<td width="25%" align="right">' . number_format($row['fest'], 2) . '</td> 
    	    	</tr>
    	    	
				<tr> 
    	    		<td></td> 
    	    		<td></td>
				 	<td width="25%" align="right"><b>aux.transporte: </b></td>
				 	<td width="25%" align="right"><b>' . number_format($row['transporte'], 2) . '</b></td> 
    	    	</tr>

					<tr> 
    	    		<td></td> 
    	    		<td></td>
				 	<td width="25%" align="right"><b>Rec. Nocturno: </b></td>
				 	<td width="25%" align="right"><b>' . number_format($row['recargo'], 2) . '</b></td> 
    	    	</tr>

					<tr> 
    	    		<td></td> 
    	    		<td></td>
				 	<td width="25%" align="right"><b>ext. diurnas: </b></td>
				 	<td width="25%" align="right"><b>' . number_format($row['ext_diu'], 2) . '</b></td> 
    	    	</tr>

				<tr> 
    	    		<td></td> 
    	    		<td></td>
				 	<td width="25%" align="right"><b>ext. nocturnas: </b></td>
				 	<td width="25%" align="right"><b>' . number_format($row['ext_noc'], 2) . '</b></td> 
    	    	</tr>

				<tr> 
    	    		<td></td> 
    	    		<td></td>
				 	<td width="25%" align="right"><b>fest. diurnas: </b></td>
				 	<td width="25%" align="right"><b>' . number_format($row['dom_diu'], 2) . '</b></td> 
    	    	</tr>

				<tr> 
    	    		<td></td> 
    	    		<td></td>
				 	<td width="25%" align="right"><b>fest. noc: </b></td>
				 	<td width="25%" align="right"><b>' . number_format($row['dom_noc'], 2) . '</b></td> 
    	    	</tr>

    	    	<tr> 
    	    		<td></td> 
    	    		<td></td>
				 	<td width="25%" align="right">Deducciones: </td>
				 	<td width="25%" align="right">' . number_format($deduction, 2) . '</td> 
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
				 	<td width="25%" align="right"><b>Total Deduciones:</b></td>
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
				 	<td width="25%" align="right"><b> -' . number_format($otros, 2) . '</b></td> 
    	    	</tr>
    	    	
    	    	<tr> 
    	    		<td></td> 
    	    		<td></td>
				 	<td width="25%" align="right"><b>Observacion:</b></td>
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
				 	<td width="25%" align="right"><b>' . number_format($row['neto'] + $pagos_descansos, 2) . '</b></td> 
    	    	</tr>

				<tr> 
    	    		<td></td> 
    	    		<td></td>
				 	<td width="25%" align="right"><b>Salud-Pension:</b></td>
				 	<td width="25%" align="right"><b>' . number_format($salud_pension, 2) . '</b></td> 
    	    	</tr>
    	    	<tr> 
    	    		<td></td> 
    	    		<td></td>
				 	<td width="25%" align="right"><b>Salario Neto:</b></td>
				 	<td width="25%" align="right"><b>' . number_format($total_pagar, 2) . '</b></td> 
    	    	</tr>
    	    </table>
    	    <br><hr>
		';
}
$pdf->writeHTML($contents);
$pdf->Output('payslip.pdf', 'I');
