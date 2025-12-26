<?php 
	include 'includes/session.php';

	if(isset($_POST['id'])){
		$id = $_POST['id'];
		$sql = "SELECT *, incapacidad.id AS caid FROM incapacidad LEFT JOIN employees on employees.id=incapacidad.employee_id WHERE incapacidad.id='$id'";
		$query = $conn->query($sql);
		$row = $query->fetch_assoc();

		echo json_encode($row);
	}
