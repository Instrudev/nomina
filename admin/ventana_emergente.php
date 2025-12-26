<?php
include 'includes/session.php';
include '../timezone.php';

// Página para activar/desactivar el pop‑up
$config_file = __DIR__ . '/popup_config.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $active = isset($_POST['active']) ? true : false;
    file_put_contents($config_file, json_encode(['active' => $active]));
    $message = 'Configuración actualizada correctamente.';
}
// Leer el estado actual
if (file_exists($config_file)) {
    $cfg = json_decode(file_get_contents($config_file), true);
    $is_active = (bool) ($cfg['active'] ?? true);
} else {
    $is_active = true;
}
?>
<?php include 'includes/header.php'; ?>
<body class="p-4">
  <div class="container">
    <h2>Popup por Mora de Pago</h2>
    <?php if (!empty($message)): ?>
      <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="form-group form-check">
        <input type="checkbox" class="form-check-input" id="active" name="active" <?= $is_active ? 'checked' : '' ?>>
        <label class="form-check-label" for="active">Activar ventana emergente por mora</label>
      </div>
      <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
  </div>
</body>
</html>