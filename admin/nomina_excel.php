<?php
header('Content-type: application/xls');
header('Content-Disposition: attachment; filename=Nomina.xls');
include 'includes/session.php';
include 'includes/conn.php';

function generateRow($from, $to, $conn) {
    // Como no usamos deducciones, se asigna 0
    $deduction = 0;
    
    /*
    // Consulta para obtener la suma de los campos individuales de la tabla attendance
    $sql = "SELECT 
                SUM(salario_base) AS salario_base, 
                SUM(festivo) AS fest, 
                SUM(aux_tran) AS transporte, 
                SUM(recargo_noc) AS recargo, 
                SUM(hora_ext_diu) AS ext_diu, 
                SUM(nocturnas) AS nocturnas, 
                SUM(hora_ext_noc) AS dom_noc, 
                SUM(num_hr) AS hr, 
                attendance.employee_id AS empid, 
                employees.employee_id AS employee, 
                employees.firstname, 
                employees.lastname, 
                employees.identification, 
                position.description AS position_name 
            FROM attendance 
            LEFT JOIN employees ON employees.id = attendance.employee_id 
            LEFT JOIN position ON position.id = employees.position_id 
            WHERE date BETWEEN '$from' AND '$to' 
            GROUP BY attendance.employee_id 
            ORDER BY employees.lastname ASC, employees.firstname ASC";
            */
     $sql = "SELECT *, SUM(salario_base) as salario_base ,SUM(aux_tran) as transporte , SUM(hora_ext_diu) as ext_diu , sum(hora_ext_noc) as ext_noc , SUM(salario_base) + SUM(aux_tran) + SUM(hora_ext_diu) + SUM(hora_ext_noc) AS neto , attendance.employee_id AS empid, employees.employee_id AS employee FROM attendance LEFT JOIN employees ON employees.id=attendance.employee_id LEFT JOIN position ON position.id=employees.position_id WHERE date BETWEEN '$from' AND '$to' GROUP BY attendance.employee_id ORDER BY employees.lastname ASC, employees.firstname ASC";
     
    $query = $conn->query($sql);
    $total = 0;

    $tabla = '<table border="1">
        <thead>
            <tr>
                <th colspan="2">Nombre Empleado</th>
                <th colspan="2">Identificación</th>
                <th colspan="2">Salario Neto</th>
                <th colspan="2">Horas Trabajadas</th>
            </tr>
        </thead>
        <tbody>';
    
    while ($row = $query->fetch_assoc()) {
        $empid = $row['empid'];
        
        // Cashadvance: Si no hay registros, se asigna 0.
        $casql = "SELECT SUM(amount) AS cashamount FROM cashadvance WHERE employee_id='$empid' AND date_advance BETWEEN '$from' AND '$to'";
        $caquery = $conn->query($casql);
        $carow = $caquery->fetch_assoc();
        $cashadvance = isset($carow['cashamount']) ? $carow['cashamount'] : 0;
        
        // Descansos
        $sql1 = "SELECT SUM(pago) AS total_pagos FROM descansos WHERE employee_id='$empid' AND fecha_desc BETWEEN '$from' AND '$to'";
        $query1 = $conn->query($sql1);
        $row1 = $query1->fetch_assoc();
        $pagos_descansos = isset($row1['total_pagos']) ? $row1['total_pagos'] : 0;
        
        // Otros
        $sql2 = "SELECT SUM(pago) AS total_otros, observacion FROM otros WHERE employee_id='$empid' AND fecha_otro BETWEEN '$from' AND '$to'";
        $query2 = $conn->query($sql2);
        $row2 = $query2->fetch_assoc();
        $otros = isset($row2['total_otros']) ? $row2['total_otros'] : 0;
        $observacion = isset($row2['observacion']) ? $row2['observacion'] : '';
        
        // Incapacidad
        $sql3 = "SELECT SUM(pago) AS total_incapacidad FROM incapacidad WHERE employee_id='$empid' AND create_at BETWEEN '$from' AND '$to'";
        $query3 = $conn->query($sql3);
        $row3 = $query3->fetch_assoc();
        $incapacidad = isset($row3['total_incapacidad']) ? $row3['total_incapacidad'] : 0;
        
        // Total deducciones: solo se toma cashadvance, pues deduction es 0.
        $total_deduction = $deduction + $cashadvance;
        
        // Calcular el Subtotal sumando las columnas individuales de attendance
        $calc_subtotal = $row['salario_base']
                       + $row['ext_diu']
                       + $row['ext_noc']
                       + $row['transporte'];
        // Calcular el Salario Neto
        $suma = $calc_subtotal + $pagos_descansos + $otros + $incapacidad - $total_deduction;
        $total += $suma;
        
        $tabla .= '<tr>
                    <td colspan="2">' . $row['lastname'] . ', ' . $row['firstname'] . '</td>
                    <td colspan="2">' . $row['identification'] . '</td>
                    <td colspan="2">' . number_format($suma, 2) . '</td>
                    <td colspan="2">' . $row['hr'] . '</td>
                   </tr>';
    }
    
    $tabla .= '<tr>
                <td colspan="2" align="right"><b>SubTotal</b></td>
                <td align="right"><b>' . number_format($total, 2) . '</b></td>
               </tr>';
    
    // Se agrega una sección adicional con información de "otros" (por ejemplo, vacaciones)
    $tabla .= '<tr>
                <th width="40%" align="center"><b>Nombre Empleado</b></th>
                <th width="30%" align="center"><b>Cédula</b></th>
                <th width="30%" align="center"><b>Salario Neto</b></th>
              </tr>';
    
    $sql4 = "SELECT * FROM otros LEFT JOIN employees ON otros.employee_id = employees.id WHERE fecha_otro BETWEEN '$from' AND '$to'";
    $query4 = $conn->query($sql4);
    $total_vacaciones = 0;
    while ($row = $query4->fetch_assoc()) {
        $total_vacaciones += $row['pago'];
        $tabla .= '<tr>
                    <td>' . $row['lastname'] . ', ' . $row['firstname'] . '</td>
                    <td>' . $row['identification'] . '</td>
                    <td align="right">' . number_format($row['pago'], 2) . '</td>
                   </tr>';
    }
    $tabla .= '<tr>
                <td colspan="2" align="right"><b>Total</b></td>
                <td align="right"><b>' . number_format($total + $total_vacaciones, 2) . '</b></td>
               </tr>
               </tbody>
              </table>';
    
    return $tabla;
}

$range = $_POST['date_range'];
$ex = explode(' - ', $range);
$from = date('Y-m-d', strtotime($ex[0]));
$to = date('Y-m-d', strtotime($ex[1]));

echo generateRow($from, $to, $conn);
?>
