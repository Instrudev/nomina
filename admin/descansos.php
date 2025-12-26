<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php include 'includes/navbar.php'; ?>
        <?php include 'includes/menubar.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    Descansos
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
                    <li>Empleados</li>
                    <li class="active">Descansos</li>
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
              <h4><i class='icon fa fa-check'></i> Éxito!</h4>
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
                                        <th>Dias</th>
                                        <th>Total</th>
                                        <th>Acción</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT *, descansos.id AS caid, employees.employee_id AS empid FROM descansos LEFT JOIN employees ON employees.id=descansos.employee_id ORDER BY fecha_desc DESC";

                                        $query = $conn->query($sql);
                                        while ($row = $query->fetch_assoc()) {
                                            echo "
                        <tr>
                          <td class='hidden'></td>
                          <td>" . date('M d, Y', strtotime($row['fecha_desc'])) . "</td>
                          <td>" . $row['empid'] . "</td>
                          <td>" . $row['firstname'] . ' ' . $row['lastname'] . "</td>
                          <td>" . $row['dias'] . "</td>
                          <td>" . number_format($row['pago'], 2) . "</td>
                          <td>
                            <button class='btn btn-success btn-sm edit btn-flat' data-id='" . $row['caid'] . "'><i class='fa fa-edit'></i> Editar</button>
                            <button class='btn btn-danger btn-sm delete btn-flat' data-id='" . $row['caid'] . "'><i class='fa fa-trash'></i> Eliminar</button>
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
        <?php include 'includes/descansos_modal.php'; ?>
    </div>
    <?php include 'includes/scripts.php'; ?>
    <script>
        $(function() {
            $("#example1").on("click", ".edit", function(e) {
                e.preventDefault();
                console.log("diste click");
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
                url: 'descansos_row.php',
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    $('.date').html(response.fecha_desc);
                    $('.employee_name').html(response.firstname + ' ' + response.lastname);
                    $('.caid').val(response.caid);
                    $('#diass').val(response.dias);
                }
            });
        }
    </script>
</body>

</html>