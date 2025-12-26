<?php 
 include 'includes/session.php';
 include '../timezone.php';
?>

<?php include 'includes/header.php'; 


$config_file = __DIR__ . '/popup_config.json';
$popup_active = true;
if (file_exists($config_file)) {
    $cfg = json_decode(file_get_contents($config_file), true);
    if (isset($cfg['active'])) {
        $popup_active = (bool) $cfg['active'];
        
    }
}


?>



<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">

    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          Asistencia
        </h1>
        <?php 
         $hoy = date("Y-m-d");
      
         $sql = "SELECT * FROM cuenta";
          $query = $conn->query($sql);
          
          while ($row = $query->fetch_assoc()) {
          $_SESSION["pago"]=$row["pague_hasta"];
          $_SESSION["estado_pago"] = $row["estado"];
          }
       
     
       if ($_SESSION["pago"] == $hoy){
    echo '<div class="alert alert-warning" role="alert">
    Estimado cliente, su factura se encuentra en mora. Agradecemos su pronta atención,para continuar usando nuestros servicios ¡Gracias por su preferencia!
      </div>';
    }

     

     ?>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
          <li class="active">Asistencia</li>
        </ol>
      </section>
      <!-- Main content -->
      <section class="content">
        <?php
        if (isset($_SESSION['error'])) {
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              " . $_SESSION['error'] . "
            </div>
          ";
          unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i>¡Proceso Exitoso!</h4>
              " . $_SESSION['success'] . "
            </div>
          ";
          unset($_SESSION['success']);
        }
        ?>
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Nuevo</a>
              </div>
              <div class="box-body">
                <table id="example1" class="table table-bordered">
                  <thead>
                    <th class="hidden"></th>
                    <th>Fecha</th>
                    <th>ID Empleado</th>
                    <th>Nombre</th>
                    <th>Hora Entrada</th>
                    <th>Hora Salida</th>
                    <th>Entrada</th>
                    <th>Horas</th>
                    <th>Total</th>
                    <th>Acción</th>
                  </thead>
                  <tbody>
                    <?php
                    $sql = "SELECT *, employees.employee_id AS empid, attendance.id AS attid FROM attendance LEFT JOIN employees ON employees.id=attendance.employee_id ORDER BY attendance.date DESC, attendance.time_in DESC";
                    $query = $conn->query($sql);
                    while ($row = $query->fetch_assoc()) {
                      $status = ($row['status']) ? '<span class="label label-warning pull-right">a tiempo</span>' : '<span class="label label-danger pull-right">tarde</span>';
                      echo "
                        <tr>
                          <td class='hidden'></td>
                          <td>" . date('M d, Y', strtotime($row['date'])) . "</td>
                          <td>" . $row['empid'] . "</td>
                          <td>" . $row['firstname'] . ' ' . $row['lastname'] . "</td>
                          <td>" . date('h:i A', strtotime($row['time_in'])) . $status . "</td>
                          <td>" . date('h:i A', strtotime($row['time_out'])) . "</td>
                           <td>" . $row['entradas']. "</td>
                          <td>" . $row['num_hr']. "</td>
                          <td>" . $row['total']. "</td>
                          <td>
                            <button class='btn btn-success btn-sm btn-flat edit' data-id='" . $row['attid'] . "'><i class='fa fa-edit'></i> Editar</button>
                            <button class='btn btn-danger btn-sm btn-flat delete' data-id='" . $row['attid'] . "'><i class='fa fa-trash'></i> Eliminar</button>
                          </td>
                        </tr>
                      ";
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/attendance_modal.php'; ?>
  </div>
  <?php include 'includes/scripts.php'; ?>
  
     <?php if ($popup_active == 1 && $_SESSION['superadmin']  != "superadmin" ): ?>
      <!-- Modal infranqueable por mora -->
      <div class="modal fade" id="moraModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="moraModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-body text-center">
              <h4>Estimado cliente, su factura se encuentra en mora.</h4>
              <p>Por favor regularice su pago para continuar usando nuestros servicios.</p>
            </div>
          </div>
        </div>
      </div>
      <script>
        $(document).ready(function(){
          $('#moraModal').modal('show');
        });
      </script>
    <?php endif; ?>
    
  <script>
    $(function() {
      $("#example1").on("click", ".edit", function(e) {
        e.preventDefault();
        $('#edit').modal('show');
        var id = $(this).data('id');
        getRow(id);
      });

      $("#example1").on("click", ".delete", function(e) {
        e.preventDefault();
        $('#delete').modal('show');
        var id = $(this).data('id');
        getRow(id);
      });
    });

    function getRow(id) {
      $.ajax({
        type: 'POST',
        url: 'attendance_row.php',
        data: {
          id: id
        },
        dataType: 'json',
        success: function(response) {
          $('#datepicker_edit').val(response.date);
          $('#attendance_date').html(response.date);
          $('#edit_time_in').val(response.time_in);
          $('#edit_time_out').val(response.time_out);
          $('#attid').val(response.attid);
          $('#employee_name').html(response.firstname + ' ' + response.lastname);
          $('#del_attid').val(response.attid);
          $('#del_employee_name').html(response.firstname + ' ' + response.lastname);
          $('#entradas').val(response.entradas);
        }
      });
    }
  </script>
</body>

</html>