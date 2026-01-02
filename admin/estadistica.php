<?php include 'includes/header.php'; ?>
<?php include 'includes/session.php'; ?>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Seguimiento Nómina</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Seguimiento Nómina</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Formulario para seleccionar el mes -->
      <form method="GET" action="">
        <div class="form-group">
          <label for="month">Selecciona un mes (YYYY-MM):</label>
          <input type="month" id="month" name="month" class="form-control" 
                 value="<?php echo isset($_GET['month']) ? $_GET['month'] : ''; ?>">
        </div>
        <button type="submit" class="btn btn-primary">Buscar</button>
      </form>
      <br>

      <?php
      // Si se ha seleccionado un mes, mostrar el empleado con mayor ganancia en ese mes
      if (isset($_GET['month']) && !empty($_GET['month'])) {
          $selected_month = $conn->real_escape_string($_GET['month']);
          $sql = "SELECT a.employee_id, e.firstname, e.lastname, SUM(a.total) AS total 
                  FROM attendance a
                  INNER JOIN employees e ON a.employee_id = e.id
                  WHERE DATE_FORMAT(a.date, '%Y-%m') = '$selected_month'
                  GROUP BY a.employee_id
                  ORDER BY total DESC
                  LIMIT 1";
          $result = $conn->query($sql);
          if ($result && $result->num_rows > 0) {
              $row = $result->fetch_assoc();
              echo "<h3>Empleado con mayor ganancia en el mes $selected_month</h3>";
              echo "<p><strong>Empleado:</strong> " . $row['firstname'] . " " . $row['lastname'] . "</p>";
              echo "<p><strong>Total Ganado:</strong> $" . number_format($row['total'], 2) . "</p>";
          } else {
              echo "<p>No se encontraron registros para el mes seleccionado.</p>";
          }
      } else {
          // Caso por defecto: se muestran la tabla y la gráfica
          
          // 1. Consulta para la tabla: total pagado por mes de cada empleado (ordenado de mayor a menor)
          $sql_table = "SELECT 
                            DATE_FORMAT(a.date, '%Y-%m') AS month, 
                            a.employee_id, 
                            CONCAT(e.firstname, ' ', e.lastname) AS full_name, 
                            SUM(a.total) AS total
                        FROM attendance a
                        INNER JOIN employees e ON a.employee_id = e.id
                        GROUP BY month, a.employee_id, full_name
                        ORDER BY month, total DESC";
          $result_table = $conn->query($sql_table);
          
          echo "<h3>Total pagado por mes de cada empleado</h3>";
          echo "<table class='table table-bordered table-striped'>";
          echo "<thead><tr><th>Mes</th><th>Empleado</th><th>Total Ganado</th></tr></thead>";
          echo "<tbody>";
          if ($result_table && $result_table->num_rows > 0) {
              while ($row = $result_table->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . $row['month'] . "</td>";
                  echo "<td>" . htmlspecialchars($row['full_name'])."</td>";
                  echo "<td>$" . number_format($row['total'], 2) . "</td>";
                  echo "</tr>";
              }
          }
          echo "</tbody></table>";
          
          // 2. Consulta para la gráfica: empleado con mayor ganancia por mes (usando NOT EXISTS)
          $sql_chart = "SELECT t.month, t.employee_id, t.total, CONCAT(e.firstname, ' ', e.lastname) AS full_name
                        FROM (
                            SELECT DATE_FORMAT(date, '%Y-%m') AS month, employee_id, SUM(total) AS total
                            FROM attendance
                            GROUP BY month, employee_id
                        ) t
                        INNER JOIN employees e ON t.employee_id = e.id
                        WHERE NOT EXISTS (
                            SELECT 1
                            FROM (
                                SELECT DATE_FORMAT(date, '%Y-%m') AS month, employee_id, SUM(total) AS total
                                FROM attendance
                                GROUP BY month, employee_id
                            ) t2
                            WHERE t2.month = t.month AND t2.total > t.total
                        )
                        ORDER BY t.month";
          $result_chart = $conn->query($sql_chart);
          $labels = [];
          $chartData = [];
          $employeeTooltips = [];
          if ($result_chart && $result_chart->num_rows > 0) {
              while ($row = $result_chart->fetch_assoc()) {
                  $labels[] = $row['month'];
                  $chartData[] = $row['total'];
                  $employeeTooltips[] = "Empleado: " . $row['full_name'] . ")";
              }
          }
          
          echo "<h3>Empleado con mayor ganancia por mes</h3>";
          echo '<canvas id="earningsChart" width="400" height="200"></canvas>';
          ?>
          <!-- Se incluye Chart.js desde CDN -->
          <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
          <script>
          // Convertir arrays PHP a JavaScript
          var labels = <?php echo json_encode($labels); ?>;
          var data = <?php echo json_encode($chartData); ?>;
          var employeeTooltips = <?php echo json_encode($employeeTooltips); ?>;
          
          var ctx = document.getElementById('earningsChart').getContext('2d');
          var earningsChart = new Chart(ctx, {
              type: 'bar',
              data: {
                  labels: labels,
                  datasets: [{
                      label: 'Ganancias',
                      data: data,
                      backgroundColor: 'rgba(54, 162, 235, 0.2)',
                      borderColor: 'rgba(54, 162, 235, 1)',
                      borderWidth: 1
                  }]
              },
              options: {
                  scales: {
                      y: {
                          beginAtZero: true
                      }
                  },
                  plugins: {
                      tooltip: {
                          callbacks: {
                              afterLabel: function(context) {
                                  var index = context.dataIndex;
                                  return employeeTooltips[index];
                              }
                          }
                      }
                  }
              }
          });
          </script>
          <?php
      }
      $conn->close();
      ?>

    </section>
    <!-- END Main content -->
  </div>
  <?php include 'includes/footer.php'; ?>

</div>
</body>
</html>
