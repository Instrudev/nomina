<?php
include 'includes/session.php';

if (isset($_POST['add'])) {
    $employee = $_POST['employee'];
    $dias = $_POST['dias'];
    $fecha = $_POST['fecha'];
    $sql = "SELECT * FROM employees WHERE employee_id = '$employee'";
    $query = $conn->query($sql);
    if ($query->num_rows < 1) {
        $_SESSION['error'] = 'Employee not found';
    } else {
        $row = $query->fetch_assoc();
        $salario = intval($row['salario']);
        echo $precio_dia = (($salario / 240) * 8) * $dias;
        $redondeo = round($precio_dia, 0);
        $employee_id = $row['id'];
        $sql = "INSERT INTO descansos (employee_id, fecha_desc, dias, pago) VALUES ('$employee_id', '$fecha', '$dias' , '$redondeo')";
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

header('location: descansos.php');

