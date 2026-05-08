<?php
require_once __DIR__ . '/auth.php';

// ── Conversión de TXT a array de reflectores ──────────────────────────────────
function parseTxt($content, $type) {
    $reflectors = [];
    foreach (explode("\n", $content) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        // Separar por tabulador o espacios múltiples
        $parts = preg_split('/[\t ]+/', $line, 2);
        if (count($parts) < 2) continue;
        $name = strtoupper(trim($parts[0]));
        $ip   = trim($parts[1]);
        if ($name === '' || $ip === '') continue;
        $reflectors[] = ['name' => $name, 'reflector_type' => $type, 'ipv4' => $ip];
    }
    return $reflectors;
}

// ── Descarga desde pistar.uk y convierte ─────────────────────────────────────
function downloadAndConvert() {
    $sources = [
        ['url' => 'http://www.pistar.uk/downloads/DExtra_Hosts.txt', 'type' => 'XRF'],
        ['url' => 'http://www.pistar.uk/downloads/DCS_Hosts.txt',    'type' => 'DCS'],
        ['url' => 'http://www.pistar.uk/downloads/DPlus_Hosts.txt',  'type' => 'REF'],
        ['url' => 'http://www.pistar.uk/downloads/XLXHosts.txt',     'type' => 'XRF'],
    ];
    $all = []; $errors = [];
    foreach ($sources as $src) {
        $content = @file_get_contents($src['url']);
        if ($content === false) {
            $errors[] = 'No se pudo descargar: ' . $src['url'];
            continue;
        }
        $parsed = parseTxt($content, $src['type']);
        $all = array_merge($all, $parsed);
    }
    // Eliminar duplicados por nombre (el último gana)
    $unique = [];
    foreach ($all as $r) $unique[$r['name']] = $r;
    ksort($unique);
    return ['reflectors' => array_values($unique), 'errors' => $errors];
}

$result = null; $jsonOut = ''; $errors = [];

// ── Acción: descarga automática ───────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'download') {
    $result = downloadAndConvert();
    $errors  = $result['errors'];
    $jsonOut = json_encode(['reflectors' => $result['reflectors']], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

// ── Acción: subir ficheros manualmente ───────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'upload') {
    $all = [];
    $map = [
        'file_dextra' => 'XRF',
        'file_dcs'    => 'DCS',
        'file_dplus'  => 'REF',
        'file_xlx'    => 'XRF',
    ];
    foreach ($map as $field => $type) {
        if (!empty($_FILES[$field]['tmp_name']) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $content = file_get_contents($_FILES[$field]['tmp_name']);
            $all = array_merge($all, parseTxt($content, $type));
        }
    }
    if (!empty($all)) {
        $unique = [];
        foreach ($all as $r) $unique[$r['name']] = $r;
        ksort($unique);
        $jsonOut = json_encode(['reflectors' => array_values($unique)], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        $errors[] = 'No se procesó ningún fichero. Sube al menos uno.';
    }
}

// ── Descarga directa del JSON generado ───────────────────────────────────────
if (isset($_GET['dl']) && $_GET['dl'] === '1' && isset($_POST['json_content'])) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="DStar_Hosts.json"');
    echo $_POST['json_content'];
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>D-Star Hosts Converter</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Rajdhani:wght@500;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
<style>
:root{--bg:#0a0e14;--surface:#111720;--border:#1e2d3d;--green:#00ff9f;--red:#ff4560;--amber:#ffb300;--cyan:#00d4ff;--dstar:#00e5ff;--violet:#b57aff;--text:#a8b9cc;--text-dim:#4a5568;--font-mono:'Share Tech Mono',monospace;--font-ui:'Rajdhani',sans-serif;}
*{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--bg);color:var(--text);font-family:var(--font-ui);min-height:100vh;}
.ctrl-header{background:var(--surface);border-bottom:1px solid var(--border);padding:1rem 2rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;}
.ctrl-header img{height:40px;width:auto;}
.ctrl-header h1{font-weight:700;font-size:1.3rem;letter-spacing:.08em;color:#e2eaf5;text-transform:uppercase;}
.btn-back{margin-left:auto;background:transparent;color:var(--dstar);border:1px solid var(--dstar);border-radius:4px;font-family:var(--font-mono);font-size:.78rem;letter-spacing:.08em;text-transform:uppercase;padding:.4rem 1.2rem;text-decoration:none;transition:background .2s;}
.btn-back:hover{background:rgba(0,229,255,.1);}
.body{padding:2rem;max-width:1000px;margin:0 auto;}
.card{background:var(--surface);border:1px solid var(--border);border-radius:8px;margin-bottom:1.5rem;overflow:hidden;}
.card-title{background:linear-gradient(90deg,#0d1e2a,#111720);border-bottom:1px solid var(--border);padding:.6rem 1.2rem;font-family:var(--font-mono);font-size:.72rem;letter-spacing:.15em;text-transform:uppercase;color:var(--dstar);}
.card-title::before{content:'▸ ';}
.card-body{padding:1.2rem;}
.btn{font-family:var(--font-mono);font-size:.8rem;letter-spacing:.08em;text-transform:uppercase;border:none;border-radius:6px;padding:.6rem 1.8rem;cursor:pointer;transition:opacity .2s;}
.btn-green{background:var(--green);color:#000;}
.btn-cyan{background:var(--dstar);color:#000;}
.btn-amber{background:var(--amber);color:#000;}
.btn:hover{opacity:.85;}
.btn:disabled{opacity:.4;cursor:not-allowed;}
.desc{font-family:var(--font-mono);font-size:.75rem;color:var(--text-dim);margin-bottom:1rem;line-height:1.6;}
.upload-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;}
@media(max-width:600px){.upload-grid{grid-template-columns:1fr;}}
.upload-item label{font-family:var(--font-mono);font-size:.65rem;color:var(--text-dim);letter-spacing:.12em;text-transform:uppercase;display:block;margin-bottom:.3rem;}
.upload-item input[type=file]{background:#0a1018;border:1px solid var(--border);border-radius:3px;color:var(--dstar);font-family:var(--font-mono);font-size:.75rem;padding:.35rem .6rem;width:100%;}
.badge{display:inline-block;font-family:var(--font-mono);font-size:.6rem;padding:.15rem .4rem;border-radius:2px;margin-left:.4rem;vertical-align:middle;}
.badge-xrf{background:rgba(0,229,255,.15);color:var(--dstar);border:1px solid rgba(0,229,255,.3);}
.badge-dcs{background:rgba(0,255,159,.15);color:var(--green);border:1px solid rgba(0,255,159,.3);}
.badge-ref{background:rgba(255,179,0,.15);color:var(--amber);border:1px solid rgba(255,179,0,.3);}
.alert{font-family:var(--font-mono);font-size:.78rem;padding:.6rem 1rem;border-radius:4px;border:1px solid;margin-bottom:.8rem;}
.alert-err{background:rgba(255,69,96,.07);border-color:var(--red);color:var(--red);}
.alert-ok{background:rgba(0,255,159,.07);border-color:var(--green);color:var(--green);}
.json-output{font-family:var(--font-mono);font-size:.72rem;color:#7a9ab5;background:#060c10;border:1px solid var(--border);border-radius:4px;padding:.8rem;height:320px;overflow-y:auto;white-space:pre;word-break:break-all;margin-bottom:1rem;}
.json-output::-webkit-scrollbar{width:4px;}
.json-output::-webkit-scrollbar-thumb{background:var(--border);}
.stats{display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:1rem;}
.stat{font-family:var(--font-mono);font-size:.75rem;}
.stat span{font-weight:bold;}
.stat-xrf span{color:var(--dstar);}
.stat-dcs span{color:var(--green);}
.stat-ref span{color:var(--amber);}
.stat-total span{color:#fff;}
.divider{border:none;border-top:1px solid var(--border);margin:1.2rem 0;}
</style>
</head>
<body>
<header class="ctrl-header">
  <img src="Logo_ea3eiz.png" alt="EA3EIZ">
  <div>
    <h1>📡 D-Star Hosts Converter</h1>
  </div>
  <a href="mmdvm.php" class="btn-back">← Volver</a>
</header>

<div class="body">

  <!-- OPCIÓN 1: Descarga automática -->
  <div class="card">
    <div class="card-title">Opción 1 · Descarga automática desde pistar.uk</div>
    <div class="card-body">
      <p class="desc">Descarga directamente DExtra_Hosts.txt, DCS_Hosts.txt, DPlus_Hosts.txt y XLXHosts.txt desde pistar.uk y los convierte al formato DStar_Hosts.json en un solo click. La Pi necesita acceso a internet.</p>
      <form method="POST">
        <input type="hidden" name="action" value="download">
        <button type="submit" class="btn btn-green">⬇ Descargar y convertir ahora</button>
      </form>
    </div>
  </div>

  <!-- OPCIÓN 2: Subir ficheros manualmente -->
  <div class="card">
    <div class="card-title">Opción 2 · Subir ficheros .txt manualmente</div>
    <div class="card-body">
      <p class="desc">Si la Pi no tiene acceso a internet, sube los ficheros desde tu ordenador. Puedes subir solo los que tengas, no es obligatorio subir los cuatro.</p>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload">
        <div class="upload-grid">
          <div class="upload-item">
            <label>DExtra_Hosts.txt <span class="badge badge-xrf">XRF</span></label>
            <input type="file" name="file_dextra" accept=".txt">
          </div>
          <div class="upload-item">
            <label>DCS_Hosts.txt <span class="badge badge-dcs">DCS</span></label>
            <input type="file" name="file_dcs" accept=".txt">
          </div>
          <div class="upload-item">
            <label>DPlus_Hosts.txt <span class="badge badge-ref">REF</span></label>
            <input type="file" name="file_dplus" accept=".txt">
          </div>
          <div class="upload-item">
            <label>XLXHosts.txt <span class="badge badge-xrf">XRF</span></label>
            <input type="file" name="file_xlx" accept=".txt">
          </div>
        </div>
        <button type="submit" class="btn btn-cyan">⚙ Convertir ficheros</button>
      </form>
    </div>
  </div>

  <!-- RESULTADO -->
  <?php if (!empty($errors)): ?>
    <?php foreach ($errors as $e): ?>
      <div class="alert alert-err">✖ <?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if ($jsonOut !== ''): ?>
  <?php
    $decoded = json_decode($jsonOut, true);
    $total = count($decoded['reflectors'] ?? []);
    $cXRF = count(array_filter($decoded['reflectors'] ?? [], fn($r) => $r['reflector_type'] === 'XRF'));
    $cDCS = count(array_filter($decoded['reflectors'] ?? [], fn($r) => $r['reflector_type'] === 'DCS'));
    $cREF = count(array_filter($decoded['reflectors'] ?? [], fn($r) => $r['reflector_type'] === 'REF'));
  ?>
  <div class="card">
    <div class="card-title">Resultado · DStar_Hosts.json generado</div>
    <div class="card-body">
      <div class="alert alert-ok">✔ JSON generado correctamente con <?= $total ?> reflectores.</div>
      <div class="stats">
        <div class="stat stat-total">Total: <span><?= $total ?></span></div>
        <div class="stat stat-xrf">XRF/XLX: <span><?= $cXRF ?></span></div>
        <div class="stat stat-dcs">DCS: <span><?= $cDCS ?></span></div>
        <div class="stat stat-ref">REF: <span><?= $cREF ?></span></div>
      </div>
      <div class="json-output"><?= htmlspecialchars($jsonOut) ?></div>
      <form method="POST" action="?dl=1">
        <input type="hidden" name="json_content" value="<?= htmlspecialchars($jsonOut) ?>">
        <button type="submit" class="btn btn-amber">💾 Descargar DStar_Hosts.json</button>
      </form>
    </div>
  </div>
  <?php endif; ?>

</div>
</body>
</html>
