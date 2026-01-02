<?php
include 'includes/session.php';
 include 'includes/header.php'; 
if(isset($_POST["guardar"])){
 $fecha_emision = $_POST["emision"];
$periodo_facturado = $_POST["facturado"];
$pague_hasta = $_POST["pago"];
$estado = $_POST["estado"];
$cuenta = "vacia";

//datos del arhivo
$fileTmpPath = $_FILES['cuenta']['tmp_name'];
$nombre_archivo = $_FILES['cuenta']['name'];
$tipo_archivo = $_FILES['cuenta']['type'];
$tamano_archivo = $_FILES['cuenta']['size'];
$fileNameCmps = explode(".", $nombre_archivo);
$fileExtension = strtolower(end($fileNameCmps));

// directory in which the uploaded file will be moved 
$uploadFileDir = '../cuentas/';
$dest_path = $uploadFileDir . $nombre_archivo;
if(move_uploaded_file($fileTmpPath, $dest_path))
{
  $message ='File is successfully uploaded.';
}
else
{
  $message = 'There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
}

$sql = "INSERT INTO cuenta (`fecha_emision`, `periodo_facturado`, `pague_hasta`, `estado`, `cuenta`) values ('$fecha_emision','$periodo_facturado','$pague_hasta','$estado','$nombre_archivo')";
echo $sql;
    $query = $conn->query($sql);
}

?>  
<form method="POST" enctype="multipart/form-data" >
   <label for="">fecha emision</label>
   <input type="date" name="emision">
   <select name="facturado" id="">
  <option value="Enero">Enero</option>
  <option value="Febrero">Febrero</option>
  <option value="Marzo">Marzo</option>
  <option value="Abril">Abril</option>
  <option value="Mayo">Mayo</option>
  <option value="Junio">Junio</option>
  <option value="Julio">Julio</option>
  <option value="Agosto">Agosto</option>
  <option value="Septiembre">Septiembre</option>
  <option value="Octubre">Octubre</option>
  <option value="Noviembre">Noviembre</option>
  <option value="Diciembre">Diciembre</option>
   </select>
   <label for="">pague hasta</label>
   <input type="date" name="pago">
   <label for="">estado</label>
   <input type="text" name="estado">
   <label for="">cargar cuenta</label>
   <input type="file" name="cuenta">
   <button name="guardar" type="submit">guardar</button>
</form>

 <table class="table table-responsive">
         <thead>
          <tr>
            <th>ID</th>
            <th>fecha Emision</th>
            <th>Periodo Facturado</th>
            <th>Page Hasta</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
         </thead>
         <tbody>
          <?php 
          $sql = "SELECT * FROM cuenta";
          $query = $conn->query($sql);
          
          while ($row = $query->fetch_assoc()) {
          $_SESSION["pago"]=$row["pague_hasta"];
          $_SESSION["estado_pago"] = $row["estado"];
          
          ?>
          <tr>
            <td><?php echo $row["id"]  ?></td>
            <td><?php echo $row["fecha_emision"] ?></td>
            <td><?php echo $row["periodo_facturado"] ?></td>
            <td><?php echo $row["pague_hasta"] ?></td>
            <td><?php echo ($row["estado"] == 0) ? "pendiente" : "pagada" ?></td>
            <td>
    <a href="editar_cuenta.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">Editar</a>
    <a href="eliminar_cuenta.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta cuenta?');">Eliminar</a>
</td>
          </tr>
          <?php
           }
          ?> 
         </tbody>
         
      </table>