<?php include 'includes/session.php'; ?>
<?php
if (!isset($_SESSION['superadmin']) || $_SESSION['superadmin'] !== 'superadmin') {
  $_SESSION['error'] = 'No tienes permisos para acceder a la configuración de nómina.';
  header('location: home.php');
  exit();
}

function asegurarTablaConfiguracionNomina($conn) {
  $sql = "CREATE TABLE IF NOT EXISTS payroll_settings (
      id INT AUTO_INCREMENT PRIMARY KEY,
      salario_minimo_mensual DECIMAL(12,2) NOT NULL,
      auxilio_transporte_mensual DECIMAL(12,2) NOT NULL,
      horas_laborales_mensuales DECIMAL(6,2) NOT NULL DEFAULT 240,
      porcentaje_nocturno DECIMAL(5,2) NOT NULL,
      porcentaje_extra_diurno DECIMAL(5,2) NOT NULL,
      porcentaje_extra_nocturno DECIMAL(5,2) NOT NULL,
      porcentaje_festivo DECIMAL(5,2) NOT NULL,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
  $conn->query($sql);

  $check = $conn->query("SELECT id FROM payroll_settings LIMIT 1");
  if ($check && $check->num_rows === 0) {
    $conn->query("INSERT INTO payroll_settings (
        salario_minimo_mensual,
        auxilio_transporte_mensual,
        horas_laborales_mensuales,
        porcentaje_nocturno,
        porcentaje_extra_diurno,
        porcentaje_extra_nocturno,
        porcentaje_festivo
      ) VALUES (
        1423500.00,
        200000.00,
        230.00,
        35.00,
        25.00,
        75.00,
        75.00
      )");
  }
}

function obtenerConfiguracionNomina($conn) {
  asegurarTablaConfiguracionNomina($conn);
  $sql = "SELECT * FROM payroll_settings ORDER BY id DESC LIMIT 1";
  $query = $conn->query($sql);
  return $query ? $query->fetch_assoc() : null;
}

function calcularTarifasNomina($settings) {
  $hora_base = round($settings['salario_minimo_mensual'] / $settings['horas_laborales_mensuales'], 2);
  $porcentaje_nocturno = $settings['porcentaje_nocturno'] / 100;
  $porcentaje_extra_diurno = $settings['porcentaje_extra_diurno'] / 100;
  $porcentaje_extra_nocturno = $settings['porcentaje_extra_nocturno'] / 100;
  $porcentaje_festivo = $settings['porcentaje_festivo'] / 100;

  return [
    'hora_ordinaria_diurna' => $hora_base,
    'hora_ordinaria_nocturna' => round($hora_base * (1 + $porcentaje_nocturno), 2),
    'hora_extra_diurna' => round($hora_base * (1 + $porcentaje_extra_diurno), 2),
    'hora_extra_nocturna' => round($hora_base * (1 + $porcentaje_extra_nocturno), 2),
    'hora_festivo_diurna' => round($hora_base * (1 + $porcentaje_festivo), 2),
    'hora_festivo_nocturna' => round($hora_base * (1 + $porcentaje_festivo + $porcentaje_nocturno), 2),
    'hora_extra_festivo_diurna' => round($hora_base * (1 + $porcentaje_festivo + $porcentaje_extra_diurno), 2),
    'hora_extra_festivo_nocturna' => round($hora_base * (1 + $porcentaje_festivo + $porcentaje_extra_nocturno), 2)
  ];
}

if (isset($_POST['save'])) {
  $salario_minimo = round((float) str_replace(',', '.', $_POST['salario_minimo_mensual']), 2);
  $auxilio_transporte = round((float) str_replace(',', '.', $_POST['auxilio_transporte_mensual']), 2);
  $horas_mensuales = round((float) str_replace(',', '.', $_POST['horas_laborales_mensuales']), 2);
  $porcentaje_nocturno = round((float) str_replace(',', '.', $_POST['porcentaje_nocturno']), 2);
  $porcentaje_extra_diurno = round((float) str_replace(',', '.', $_POST['porcentaje_extra_diurno']), 2);
  $porcentaje_extra_nocturno = round((float) str_replace(',', '.', $_POST['porcentaje_extra_nocturno']), 2);
  $porcentaje_festivo = round((float) str_replace(',', '.', $_POST['porcentaje_festivo']), 2);

  $settings = obtenerConfiguracionNomina($conn);
  if ($settings) {
    $sql = "UPDATE payroll_settings SET 
        salario_minimo_mensual = $salario_minimo,
        auxilio_transporte_mensual = $auxilio_transporte,
        horas_laborales_mensuales = $horas_mensuales,
        porcentaje_nocturno = $porcentaje_nocturno,
        porcentaje_extra_diurno = $porcentaje_extra_diurno,
        porcentaje_extra_nocturno = $porcentaje_extra_nocturno,
        porcentaje_festivo = $porcentaje_festivo
      WHERE id = '{$settings['id']}'";
  } else {
    $sql = "INSERT INTO payroll_settings (
        salario_minimo_mensual,
        auxilio_transporte_mensual,
        horas_laborales_mensuales,
        porcentaje_nocturno,
        porcentaje_extra_diurno,
        porcentaje_extra_nocturno,
        porcentaje_festivo
      ) VALUES (
        $salario_minimo,
        $auxilio_transporte,
        $horas_mensuales,
        $porcentaje_nocturno,
        $porcentaje_extra_diurno,
        $porcentaje_extra_nocturno,
        $porcentaje_festivo
      )";
  }

  if ($conn->query($sql)) {
    $_SESSION['success'] = 'Configuración de nómina actualizada correctamente.';
  } else {
    $_SESSION['error'] = 'No se pudo actualizar la configuración de nómina.';
  }
  header('location: payroll_settings.php');
  exit();
}

$settings = obtenerConfiguracionNomina($conn);
$tarifas = $settings ? calcularTarifasNomina($settings) : [];
$auxilio_diario = $settings ? round($settings['auxilio_transporte_mensual'] / 30, 2) : 0;
?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Configuración de Nómina</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Configuración de Nómina</li>
      </ol>
    </section>

    <section class="content">
      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i>¡Proceso Exitoso!</h4>
              ".$_SESSION['success']."
            </div>
          ";
          unset($_SESSION['success']);
        }
      ?>
      <div class="row">
        <div class="col-md-6">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Parámetros Base</h3>
            </div>
            <form method="POST" action="payroll_settings.php">
              <div class="box-body">
                <div class="form-group">
                  <label>Salario mínimo mensual</label>
                  <input type="number" step="0.01" class="form-control" name="salario_minimo_mensual" value="<?php echo htmlspecialchars($settings['salario_minimo_mensual'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="form-group">
                  <label>Auxilio de transporte mensual</label>
                  <input type="number" step="0.01" class="form-control" name="auxilio_transporte_mensual" value="<?php echo htmlspecialchars($settings['auxilio_transporte_mensual'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="form-group">
                  <label>Horas laborales mensuales</label>
                  <input type="number" step="0.01" class="form-control" name="horas_laborales_mensuales" value="<?php echo htmlspecialchars($settings['horas_laborales_mensuales'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="form-group">
                  <label>Recargo nocturno (%)</label>
                  <input type="number" step="0.01" class="form-control" name="porcentaje_nocturno" value="<?php echo htmlspecialchars($settings['porcentaje_nocturno'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="form-group">
                  <label>Recargo extra diurno (%)</label>
                  <input type="number" step="0.01" class="form-control" name="porcentaje_extra_diurno" value="<?php echo htmlspecialchars($settings['porcentaje_extra_diurno'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="form-group">
                  <label>Recargo extra nocturno (%)</label>
                  <input type="number" step="0.01" class="form-control" name="porcentaje_extra_nocturno" value="<?php echo htmlspecialchars($settings['porcentaje_extra_nocturno'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="form-group">
                  <label>Recargo festivo/dominical (%)</label>
                  <input type="number" step="0.01" class="form-control" name="porcentaje_festivo" value="<?php echo htmlspecialchars($settings['porcentaje_festivo'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
              </div>
              <div class="box-footer">
                <button type="submit" name="save" class="btn btn-primary"><i class="fa fa-save"></i> Guardar</button>
              </div>
            </form>
          </div>
        </div>
        <div class="col-md-6">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Valores Calculados</h3>
            </div>
            <div class="box-body">
              <table class="table table-bordered">
                <tbody>
                  <tr>
                    <th>Hora ordinaria diurna</th>
                    <td><?php echo number_format($tarifas['hora_ordinaria_diurna'], 2); ?></td>
                  </tr>
                  <tr>
                    <th>Hora ordinaria nocturna</th>
                    <td><?php echo number_format($tarifas['hora_ordinaria_nocturna'], 2); ?></td>
                  </tr>
                  <tr>
                    <th>Hora extra diurna</th>
                    <td><?php echo number_format($tarifas['hora_extra_diurna'], 2); ?></td>
                  </tr>
                  <tr>
                    <th>Hora extra nocturna</th>
                    <td><?php echo number_format($tarifas['hora_extra_nocturna'], 2); ?></td>
                  </tr>
                  <tr>
                    <th>Hora festiva/dominical diurna</th>
                    <td><?php echo number_format($tarifas['hora_festivo_diurna'], 2); ?></td>
                  </tr>
                  <tr>
                    <th>Hora festiva/dominical nocturna</th>
                    <td><?php echo number_format($tarifas['hora_festivo_nocturna'], 2); ?></td>
                  </tr>
                  <tr>
                    <th>Hora extra festiva diurna</th>
                    <td><?php echo number_format($tarifas['hora_extra_festivo_diurna'], 2); ?></td>
                  </tr>
                  <tr>
                    <th>Hora extra festiva nocturna</th>
                    <td><?php echo number_format($tarifas['hora_extra_festivo_nocturna'], 2); ?></td>
                  </tr>
                  <tr>
                    <th>Auxilio transporte diario</th>
                    <td><?php echo number_format($auxilio_diario, 2); ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>
