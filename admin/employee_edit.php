<?php
include 'includes/session.php';

if (isset($_POST['edit'])) {
	$empid = $_POST['id'];
	$firstname = $_POST['firstname'];
	$lastname = $_POST['lastname'];
	$salario_base = $_POST['salario_base'];
	$address = $_POST['address'];
	$identification = $_POST['identification'];
	$birthdate = $_POST['birthdate'];
	$contact = $_POST['contact'];
	$gender = $_POST['gender'];
	$position = $_POST['position'];
	$schedule = $_POST['schedule'];
	// problema con el address pendiente solucionar .
	$sql = "UPDATE employees SET firstname = '$firstname', lastname = '$lastname', salario= '$salario_base', addres= '$address', identification=$identification ,  birthdate = '$birthdate', contact_info = '$contact', gender = '$gender', position_id = '$position', schedule_id = '$schedule' WHERE id = '$empid'";

	if ($conn->query($sql)) {
		$_SESSION['success'] = 'Empleado actualizado con éxito';
	} else {
		$_SESSION['error'] = $conn->error;
	}
} else {
	$_SESSION['error'] = 'Seleccionar empleado para editar primero';
}

header('location: employee.php');
