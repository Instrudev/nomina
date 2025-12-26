<?php
include 'includes/session.php';
setlocale(LC_TIME, "es_CO");
if (isset($_POST['add'])) {
    
    $employee = $_POST['employee'];
    $dias = $_POST['dias'];
    $desde = $_POST['desde'];
    $hasta = $_POST['hasta'];
    $sql = "SELECT * FROM employees WHERE employee_id = '$employee'";
    $query = $conn->query($sql);
    if ($query->num_rows < 1) {
        $_SESSION['error'] = 'Employee not found';
    } else {
        $row = $query->fetch_assoc();
        $salario = intval($row['salario']);
        $precio_dia = (($salario / 240) * 8) * 0.6666;
        $precio_dia = $precio_dia * $dias;
        echo $redondeo = round($precio_dia, 0);
        $employee_id = $row['id'];
        $sql = "INSERT INTO incapacidad (employee_id, fecha_desde, fecha_hasta ,dias, create_at, pago) VALUES ('$employee_id','$desde','$hasta', '$dias' ,NOW(), '$redondeo')";
        echo $sql;
        if ($conn->query($sql)) {
            $_SESSION['success'] = 'added successfully';
        } else {
            $_SESSION['error'] = $conn->error;
        }
    }
} else {
    $_SESSION['error'] = 'Fill up add form first';
}

header('location: incapacidad.php');
