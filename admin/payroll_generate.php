<?php
include 'includes/session.php';


function generateRow($from, $to, $conn, $deduction)
{
	$contents = '';

	// $sql = "SELECT *, sum(num_hr) AS total_hr, attendance.employee_id AS empid FROM attendance LEFT JOIN employees ON employees.id=attendance.employee_id LEFT JOIN position ON position.id=employees.position_id WHERE date BETWEEN '$from' AND '$to' GROUP BY attendance.employee_id ORDER BY employees.lastname ASC, employees.firstname ASC";
	$sql = "SELECT *, SUM(salario_base) as salario_base , SUM(festivo) as fest ,SUM(aux_tran) as transporte , SUM(recargo_noc) as recargo , SUM(hora_ext_diu) as ext_diu, SUM(hora_ext_noc) as ext_noc, SUM(hora_ext_dom_diu) as dom_diu , sum(hora_ext_dom_noc) as dom_noc , SUM(total) as neto, attendance.employee_id AS empid, employees.employee_id AS employee FROM attendance LEFT JOIN employees ON employees.id=attendance.employee_id LEFT JOIN position ON position.id=employees.position_id WHERE date BETWEEN '$from' AND '$to' GROUP BY attendance.employee_id ORDER BY employees.lastname ASC, employees.firstname ASC";
	$query = $conn->query($sql);





	$total = 0;


	while ($row = $query->fetch_assoc()) {
		$empid = $row['empid'];

		$casql = "SELECT *, SUM(amount) AS cashamount FROM cashadvance WHERE employee_id='$empid' AND date_advance BETWEEN '$from' AND '$to'";


		$caquery = $conn->query($casql);
		$carow = $caquery->fetch_assoc();
		$cashadvance = $carow['cashamount'];



		$total_deduction = $deduction + $cashadvance;

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

		$incapacidad = $row3['total_incapacidad'];
		// fin otros
		$pagos_descansos = $row1['total_pagos'];

		$total_deduction = $deduction + $cashadvance + $otros;
		$suma = $row['neto'] + $pagos_descansos + $incapacidad  - $total_deduction;
		$subtotal = $row['neto'];

		$total += $suma;



		$contents .= '
			<tr>
				<td>' . $row['lastname'] . ', ' . $row['firstname'] . '</td>
				<td>' . $row['identification'] . '</td>
				<td align="right">' . number_format($suma, 2) . '</td>
			</tr>
			';
	}

	$contents .= '
			<tr>
				<td colspan="2" align="right"><b>SubTotal</b></td>
				<td align="right"><b>' . number_format($total, 2) . '</b></td>
			</tr>
				<h2 align="center">Vacaciones</h2>
			    <tr>  
           		<th width="40%" align="center"><b>Nombre Empleado</b></th>
                <th width="30%" align="center"><b>Cedula</b></th>
				<th width="30%" align="center"><b>Salario Neto</b></th> 
				
				
           </tr> 
		';
	$sql4 = "SELECT * FROM `otros` left JOIN employees on otros.employee_id = employees.id WHERE  fecha_otro BETWEEN '$from' AND '$to'  ";
	$query4 = $conn->query($sql4);

	$total_vacaciones = 0;
	while ($row = $query4->fetch_assoc()) {
		$total_vacaciones += $row['pago'];
		$contents .= '
			<tr>
				<td>' . $row['lastname'] . ', ' . $row['firstname'] . '</td>
				<td>' . $row['identification'] . '</td>
				<td align="right">' . number_format($row['pago'], 2) . '</td>
			</tr>
			';
	}
	$contents .= '
	<h2 align="center">Total</h2>
			<tr>
				<td colspan="2" align="right"><b>Total</b></td>
				<td align="right"><b>' . number_format($total + $total_vacaciones, 2) . '</b></td>
			</tr>';
	return $contents;
}

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
$pdf->SetTitle('Nomina: ' . $from_title . ' - ' . $to_title);
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
$content = '';
$content .= '
      	<h2 align="center">Nomina Guacamayas</h2>
      	<h4 align="center">' . $from_title . " - " . $to_title . '</h4>
      	<table border="1" cellspacing="0" cellpadding="3">  
           <tr>  
           		<th width="40%" align="center"><b>Nombre Empleado</b></th>
                <th width="30%" align="center"><b>Cedula</b></th>
				<th width="30%" align="center"><b>Salario Neto</b></th> 
				
				
           </tr>  
		   
		   
      ';

$content .= generateRow($from, $to, $conn, $deduction);



$content .= '</table>';
$pdf->writeHTML($content);
$pdf->Output('payroll.pdf', 'I');
