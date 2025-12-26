<?php
include 'includes/session.php';



if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $observacion = $_POST['observacion'];
    $pago = $_POST['pago'];
    $sql1 = "SELECT * FROM otros WHERE id = '$id'";
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

        $redondeo = round($pago, 0);
        $employee_id = $row['id'];
        $sql = "UPDATE otros SET observacion = '$observacion', pago='$redondeo' WHERE id = '$id'";
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


header('location:otros.php');
