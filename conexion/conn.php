<?php
	$conn = new mysqli('localhost', 'root', '', 'u442853790_nomina');

	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	
?>