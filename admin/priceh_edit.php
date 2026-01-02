<?php
	include 'includes/session.php';

	if(isset($_POST['edit'])){
		$nombre = $_POST['nombre'];
		$valor = $_POST['valor'];
		$id = $_POST['id'];
		$sql = "UPDATE price_h SET nombre = '$nombre', valor = '$valor'  WHERE id = '$id'";
		if($conn->query($sql)){
			$_SESSION['success'] = 'Attendance updated successfully';

			
		
		

		
		}
		else{
			$_SESSION['error'] = $conn->error;
		}
	}
	
	header('location:horas.php');

?>