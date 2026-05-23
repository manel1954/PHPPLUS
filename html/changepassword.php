<?php
/**
 * changepassword.php
 * Cambiar contraseña del login del panel MMDVM.
 * Guarda en /var/www/html/password.json y /home/pi/A108/html/password.json
 */

$pwdFile1 = '/var/www/html/password.json';
$pwdFile2 = '/home/pi/A108/html/password.json';

$msg  = '';
$type = '';

function loadUsers(string $file): array {
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?: [];
}

function saveUsers(string $file1, string $file2, array $data): void {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($file1, $json);
    file_put_contents($file2, $json);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user    = trim($_POST['usuario']    ?? '');
    $current = $_POST['actual']          ?? '';
    $new1    = $_POST['nueva']           ?? '';
    $new2    = $_POST['confirmar']       ?? '';

    if (!$user || !$current || !$new1 || !$new2) {
        $msg  = 'Todos los campos son obligatorios.';
        $type = 'error';
    } elseif ($new1 !== $new2) {
        $msg  = 'Las contraseñas nuevas no coinciden.';
        $type = 'error';
    } elseif (strlen($new1) < 6) {
        $msg  = 'La contraseña debe tener al menos 6 caracteres.';
        $type = 'error';
    } else {
        $users = loadUsers($pwdFile1);
        if (!isset($users[$user])) {
            $msg  = "Usuario «$user» no encontrado.";
            $type = 'error';
        } elseif (!password_verify($current, $users[$user])) {
            $msg  = 'La contraseña actual no es correcta.';
            $type = 'error';
        } else {
            $users[$user] = password_hash($new1, PASSWORD_BCRYPT);
            saveUsers($pwdFile1, $pwdFile2, $users);
            $msg  = "✔ Contraseña de «$user» actualizada correctamente en ambos ficheros.";
            $type = 'ok';
        }
    }
}

// Leer usuarios disponibles
$users    = loadUsers($pwdFile1);
$userList = array_keys($users);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🔑 Cambiar Contraseña</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
<style>
  :root {
    --bg:        #060810;
    --card:      #0a0e18;
    --text:      #1a7ac5;
    --text-light:#50a0e0;
    --border:    #102038;
    --input-bg:  #0c121e;
    --ok:        #00c853;
    --err:       #ff4444;
    --white:     #e0eaf8;
    --muted:     #3a5070;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: var(--bg);
    color: var(--white);
    font-family: 'Share Tech Mono', monospace;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
  }

  .wrapper {
    width: 100%;
    max-width: 480px;
  }

  /* ── Header ── */
  .page-header {
    text-align: center;
    margin-bottom: 2rem;
  }
  .page-header h1 {
    font-family: 'Orbitron', sans-serif;
    font-size: 1.3rem;
    color: var(--text-light);
    text-shadow: 0 0 14px rgba(26,122,197,.5);
    letter-spacing: 3px;
    margin-bottom: .4rem;
  }
  .page-header p {
    font-size: .75rem;
    color: var(--muted);
    letter-spacing: 1px;
  }

  /* ── Card ── */
  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-top: 3px solid var(--text);
    padding: 2rem;
    box-shadow: 0 0 30px rgba(26,122,197,.1);
  }

  /* ── Mensaje ── */
  .msg {
    padding: .75rem 1rem;
    margin-bottom: 1.5rem;
    font-size: .85rem;
    border-left: 3px solid;
  }
  .msg.ok  { border-color: var(--ok);  color: var(--ok);  background: rgba(0,200,83,.08); }
  .msg.err { border-color: var(--err); color: var(--err); background: rgba(255,68,68,.08); }

  /* ── Form ── */
  .form-group { margin-bottom: 1.2rem; }
  .form-group label {
    display: block;
    font-size: .72rem;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: .4rem;
  }

  .form-group select,
  .form-group input[type="password"],
  .form-group input[type="text"] {
    width: 100%;
    background: var(--input-bg);
    border: 1px solid var(--border);
    color: var(--white);
    font-family: 'Share Tech Mono', monospace;
    font-size: .9rem;
    padding: .6rem .8rem;
    outline: none;
    transition: border-color .2s;
    appearance: none;
  }
  .form-group select:focus,
  .form-group input:focus {
    border-color: var(--text);
    box-shadow: 0 0 8px rgba(26,122,197,.25);
  }

  /* Input con toggle ojo */
  .input-eye {
    position: relative;
  }
  .input-eye input {
    padding-right: 2.5rem;
  }
  .eye-btn {
    position: absolute;
    right: .6rem; top: 50%;
    transform: translateY(-50%);
    background: none; border: none;
    color: var(--muted); cursor: pointer;
    font-size: 1rem; padding: 0;
    line-height: 1;
  }
  .eye-btn:hover { color: var(--text-light); }

  /* ── Botones ── */
  .btn-submit {
    width: 100%;
    padding: .75rem;
    background: var(--text);
    color: #fff;
    border: none;
    font-family: 'Orbitron', sans-serif;
    font-size: .85rem;
    letter-spacing: 2px;
    cursor: pointer;
    margin-top: .5rem;
    transition: opacity .2s, box-shadow .2s;
  }
  .btn-submit:hover {
    opacity: .85;
    box-shadow: 0 0 16px rgba(26,122,197,.4);
  }

  .btn-close {
    display: block;
    text-align: center;
    margin-top: 1rem;
    padding: .55rem;
    background: transparent;
    border: 1px solid var(--muted);
    color: var(--muted);
    font-family: 'Share Tech Mono', monospace;
    font-size: .8rem;
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
  }
  .btn-close:hover {
    border-color: var(--err);
    color: var(--err);
  }

  /* ── Info ficheros ── */
  .files-info {
    margin-top: 1.5rem;
    font-size: .7rem;
    color: var(--muted);
    border-top: 1px solid var(--border);
    padding-top: 1rem;
  }
  .files-info span { color: var(--text-light); }

  /* Separador decorativo */
  .sep {
    height: 1px;
    background: linear-gradient(to right, transparent, var(--border), transparent);
    margin: 1.5rem 0;
  }
</style>
</head>
<body>

<div class="wrapper">

  <div class="page-header">
    <h1>🔑 CAMBIAR CONTRASEÑA</h1>
    <p>PANEL MMDVM · EA3EIZ · ADER</p>
  </div>

  <div class="card">

    <?php if ($msg): ?>
      <div class="msg <?= $type === 'ok' ? 'ok' : 'err' ?>">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" onsubmit="return validateForm()">

      <div class="form-group">
        <label>Usuario</label>
        <select name="usuario" id="usuario" required>
          <option value="">— selecciona —</option>
          <?php foreach ($userList as $u): ?>
            <option value="<?= htmlspecialchars($u) ?>"
              <?= (($_POST['usuario'] ?? '') === $u) ? 'selected' : '' ?>>
              <?= htmlspecialchars($u) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="sep"></div>

      <div class="form-group">
        <label>Contraseña actual</label>
        <div class="input-eye">
          <input type="password" name="actual" id="actual"
                 autocomplete="current-password" required>
          <button type="button" class="eye-btn" onclick="toggleEye('actual', this)">👁</button>
        </div>
      </div>

      <div class="form-group">
        <label>Contraseña nueva</label>
        <div class="input-eye">
          <input type="password" name="nueva" id="nueva"
                 autocomplete="new-password" required minlength="6">
          <button type="button" class="eye-btn" onclick="toggleEye('nueva', this)">👁</button>
        </div>
      </div>

      <div class="form-group">
        <label>Confirmar contraseña nueva</label>
        <div class="input-eye">
          <input type="password" name="confirmar" id="confirmar"
                 autocomplete="new-password" required minlength="6">
          <button type="button" class="eye-btn" onclick="toggleEye('confirmar', this)">👁</button>
        </div>
      </div>

      <button type="submit" class="btn-submit">💾 GUARDAR CONTRASEÑA</button>

    </form>

    <a href="#" onclick="window.close(); return false;" class="btn-close">✕ CERRAR</a>

    <div class="files-info">
      Guarda en:<br>
      <span><?= $pwdFile1 ?></span><br>
      <span><?= $pwdFile2 ?></span>
    </div>

  </div><!-- /card -->
</div><!-- /wrapper -->

<script>
