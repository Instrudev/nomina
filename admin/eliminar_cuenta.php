<?php
include 'includes/session.php';

if(isset($_GET['id'])){
    $id = $_GET['id'];
    $sql = "DELETE FROM cuenta WHERE id = $id";

    if($conn->query($sql)){
        header('location: cargar_cuenta.php');
    } else {
        echo "Error al eliminar la cuenta";
    }
}
?>
