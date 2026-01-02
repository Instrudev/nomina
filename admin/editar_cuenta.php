<?php
include 'includes/session.php';
include 'includes/header.php';

if(isset($_GET['id'])){
    $id = $_GET['id'];
    $sql = "SELECT * FROM cuenta WHERE id = $id";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();
}

if(isset($_POST['actualizar'])){
    $fecha_emision = $_POST['emision'];
    $periodo_facturado = $_POST['facturado'];
    $pague_hasta = $_POST['pago'];
    $estado = $_POST['estado'];

    // Si se selecciona un nuevo archivo
    if (!empty($_FILES['cuenta']['name'])) {
        $fileTmpPath = $_FILES['cuenta']['tmp_name'];
        $nombre_archivo = $_FILES['cuenta']['name'];
        $uploadFileDir = '../cuentas/';
        $dest_path = $uploadFileDir . $nombre_archivo;
        move_uploaded_file($fileTmpPath, $dest_path);
        $sql = "UPDATE cuenta SET fecha_emision='$fecha_emision', periodo_facturado='$periodo_facturado', pague_hasta='$pague_hasta', estado='$estado', cuenta='$nombre_archivo' WHERE id=$id";
    } else {
        $sql = "UPDATE cuenta SET fecha_emision='$fecha_emision', periodo_facturado='$periodo_facturado', pague_hasta='$pague_hasta', estado='$estado' WHERE id=$id";
    }

    if($conn->query($sql)){
        header('location: cargar_cuenta.php');
    } else {
        echo "Error al actualizar";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <label for="">Fecha Emisión</label>
    <input type="date" name="emision" value="<?php echo $row['fecha_emision']; ?>">
    <select name="facturado">
        <option value="Enero" <?php if($row['periodo_facturado']=='Enero') echo 'selected'; ?>>Enero</option>
        <option value="Febrero"<?php if($row['periodo_facturado']=='Febrero') echo 'selected'; ?>>Febrero</option>
        <option value="Marzo"<?php if($row['periodo_facturado']=='Marzo') echo 'selected'; ?>>Marzo</option>
        <option value="Abril"<?php if($row['periodo_facturado']=='Abril') echo 'selected'; ?>>Abril</option>
        <option value="Mayo"<?php if($row['periodo_facturado']=='Mayo') echo 'selected'; ?>>Mayo</option>
        <option value="Junio"<?php if($row['periodo_facturado']=='Junio') echo 'selected'; ?>>Junio</option>
        <option value="Julio"<?php if($row['periodo_facturado']=='Julio') echo 'selected'; ?>>Julio</option>
        <option value="Agosto"<?php if($row['periodo_facturado']=='Agosto') echo 'selected'; ?>>Agosto</option>
        <option value="Septiembre"<?php if($row['periodo_facturado']=='Septiembre') echo 'selected'; ?>>Septiembre</option>
        <option value="Octubre"<?php if($row['periodo_facturado']=='Octubre') echo 'selected'; ?>>Octubre</option>
        <option value="Noviembre"<?php if($row['periodo_facturado']=='Noviembre') echo 'selected'; ?>>Noviembre</option>
        <option value="Diciembre"<?php if($row['periodo_facturado']=='Diciembre') echo 'selected'; ?>>Diciembre</option>
        <!-- Añade el resto de los meses de la misma manera -->
    </select>
    <label for="">Pague Hasta</label>
    <input type="date" name="pago" value="<?php echo $row['pague_hasta']; ?>">
    <label for="">Estado</label>
    <input type="text" name="estado" value="<?php echo $row['estado']; ?>">
    <label for="">Cargar Cuenta</label>
    <input type="file" name="cuenta">
    <button name="actualizar" type="submit">Actualizar</button>
</form>
