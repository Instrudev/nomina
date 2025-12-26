<?php 
	include 'includes/session.php';

	if(isset($_POST['id'])){
		$id = $_POST['id'];
		$sql = "SELECT *, descansos.id AS caid FROM descansos LEFT JOIN employees on employees.id=descansos.employee_id WHERE descansos.id='$id'";
		$query = $conn->query($sql);
		$row = $query->fetch_assoc();

		echo json_encode($row);
	}
