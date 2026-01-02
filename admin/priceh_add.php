<?php
	include 'includes/session.php';

	if(isset($_POST['add'])){
		$nombre = $_POST['nombre'];
		$valor = $_POST['valor'];
		

		
				$sql = "INSERT INTO price_h (nombre, valor) VALUES ('$nombre', '$valor')";
				if($conn->query($sql)){
					$_SESSION['success'] = 'Attendance added successfully';
					
                }else{
					$_SESSION['error'] = $conn->error;
				}
			}
		
	
	else{
		$_SESSION['error'] = 'Fill up add form first';
	}
	
	header('location: horas.php');

?>