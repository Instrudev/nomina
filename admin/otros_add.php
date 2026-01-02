<?php
include 'includes/session.php';

if (isset($_POST['add'])) {
    $employee = $_POST['employee'];
    $observacion = $_POST['observacion'];
    $pago = $_POST['pago'];

    $sql = "SELECT * FROM employees WHERE employee_id = '$employee'";
    $query = $conn->query($sql);
    if ($query->num_rows < 1) {
        $_SESSION['error'] = 'Employee not found';
    } else {
        $row = $query->fetch_assoc();

        $redondeo = round($pago, 0);
        $employee_id = $row['id'];
        $sql = "INSERT INTO otros (employee_id, fecha_otro, observacion, pago) VALUES ('$employee_id', NOW(), '$observacion' , '$redondeo')";
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

header('location: otros.php');
