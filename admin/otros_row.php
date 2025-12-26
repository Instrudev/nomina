<?php
include 'includes/session.php';

if (isset($_POST['id'])) {
	$id = $_POST['id'];
	$sql = "SELECT *, otros.id AS caid FROM otros LEFT JOIN employees on employees.id=otros.employee_id WHERE otros.id='$id'";
	$query = $conn->query($sql);
	$row = $query->fetch_assoc();

	echo json_encode($row);
}
