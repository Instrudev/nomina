<?php
include 'includes/session.php';

if (isset($_POST['add'])) {
	$id_employee = $_POST['id_employe'];
	$salario_base = $_POST['salario_base'];
	$firstname = $_POST['firstname'];
	$lastname = $_POST['lastname'];
	$address = $_POST['address'];
	$identificacion = $_POST['identification'];
	$birthdate = $_POST['birthdate'];
	$contact = $_POST['contact'];
	$gender = $_POST['gender'];
	$position = $_POST['position'];
	$schedule = $_POST['schedule'];
	$filename = $_FILES['photo']['name'];
	if (!empty($filename)) {
		move_uploaded_file($_FILES['photo']['tmp_name'], '../images/' . $filename);
	}
	//creating employeeid
	$letters = '';
	$numbers = '';
	foreach (range('A', 'Z') as $char) {
		$letters .= $char;
	}
	for ($i = 0; $i < 10; $i++) {
		$numbers .= $i;
	}
	$employee_id = substr(str_shuffle($letters), 0, 3) . substr(str_shuffle($numbers), 0, 9);
	//
	$sql = "INSERT INTO employees (employee_id, salario, firstname, lastname, addres, identification, birthdate, contact_info, gender, position_id, schedule_id, photo, created_on) VALUES ('$id_employee', '$salario_base', '$firstname', '$lastname', '$address', '$identificacion', '$birthdate', '$contact', '$gender', '$position', '$schedule', '$filename', NOW())";
	if ($conn->query($sql)) {
		$_SESSION['success'] = 'Empleado añadido satisfactoriamente';
	} else {
		$_SESSION['error'] = $conn->error;
	}
} else {
	$_SESSION['error'] = 'Fill up add form first';
}

header('location: employee.php');
