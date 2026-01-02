<?php
include 'includes/session.php';



if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $dias = $_POST['dias'];
    $desde = $_POST['desde'];
    $hasta = $_POST['hasta'];
    $sql1 = "SELECT * FROM incapacidad WHERE id = '$id'";
    $sql1;
    $query1 = $conn->query($sql1);
    $row1 = $query1->fetch_assoc();
    $empleado = $row1['employee_id'];
    $sql = "SELECT * FROM employees WHERE id = $empleado";
    $sql;
    $query = $conn->query($sql);
    if ($query->num_rows < 1) {
        $_SESSION['error'] = 'Employee not found';
    } else {
        $row = $query->fetch_assoc();
        echo $salario = intval($row['salario']);
        $precio_dia = (($salario / 240) * 8) * 0.6666;
        $precio_dia = $precio_dia * $dias;
        $redondeo = round($precio_dia, 0);
        $employee_id = $row['id'];
        $sql = "UPDATE incapacidad SET fecha_desde='$desde', fecha_hasta='$hasta', dias = '$dias', pago='$redondeo' WHERE id = '$id'";
        $sql;
        if ($conn->query($sql)) {
            $_SESSION['success'] = 'added successfully';
        } else {
            $_SESSION['error'] = $conn->error;
        }
    }
} else {
    $_SESSION['error'] = 'Fill up edit form first';
}


header('location:incapacidad.php');
