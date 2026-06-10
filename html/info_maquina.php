<?php
require_once __DIR__ . '/auth.php';
header('X-Content-Type-Options: nosniff');

$JSON_FILE = __DIR__ . '/info_maquina.json';
$action = $_GET['action'] ?? '';

// ── API ───────────────────────────────────────────────────────────────────────
if ($action === 'read') {
    header('Content-Type: application/json');
    if (file_exists($JSON_FILE)) {
        $data = json_decode(file_get_contents($JSON_FILE), true);
        echo json_encode(['ok' => true, 'data' => $data]);
    } else {
        echo json_encode(['ok' => true, 'data' => ['ip' => '', 'nombre' => '']]);
    }
    exit;
}

if ($action === 'save') {
    $raw    = json_decode(file_get_contents('php://input'), true);
    $ip     = trim($raw['ip']     ?? '');
    $nombre = trim($raw['nombre'] ?? '');

    // Validación básica IP
    if ($ip !== '' && !filter_var($ip, FILTER_VALIDATE_IP)) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'msg' => 'Dirección IP no válida']);
        exit;
    }

    $payload = [
        'ip'     => $ip,
        'nombre' => $nombre,
    ];

    $result = file_put_contents($JSON_FILE, json_encode($payload, JSON_PRETTY_PRINT));
    header('Content-Type: application/json');
    echo json_encode([
        'ok'  => $result !== false,
        'msg' => $result !== false ? 'Guardado correctamente' : 'Error al escribir el fichero',
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Info Máquina · PHPPLUS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Rajdhani:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<style>
:root {
    --bg:       #00004d;
    --surface:  #111720;
    --border:   #1e2d3d;
    --green:    #00ff9f;
    --red:      #ff4560;
    --amber:    #ffb300;
    --cyan:     #00d4ff;
    --text:     #a8b9cc;
    --text-dim: #4a5568;
    --font-mono: 'Share Tech Mono', monospace;
    --font-ui:   'Rajdhani', sans-serif;
    --font-orb:  'Orbitron', monospace;
}
* { box-sizing: border-box; }
body {
    background: var(--bg);
    color: var(--text);
    font-family: var(--font-ui);
    font-size: 1rem;
    min-height: 100vh;
    margin: 0; padding: 0;
}

/* Header */
.ctrl-header { border-bottom: 2px solid var(--cyan); background: #000; }
.ctrl-header-inner {
    max-width: 700px; width: 100%; margin: 0 auto;
    padding: 1rem 2rem;
    display: flex; align-items: center; gap: .8rem;
}

/* Card */
.card-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 2rem;
    max-width: 700px;
    margin: 2.5rem auto;
}
.card-title {
    font-family: var(--font-mono);
    font-size: .7rem;
    color: var(--cyan);
    letter-spacing: .15em;
    text-transform: uppercase;
    margin-bottom: 1.8rem;
    border-bottom: 1px solid #1e2d3d;
    padding-bottom: .7rem;
}

/* Campos */
.field-label {
    font-family: var(--font-mono);
    font-size: .65rem;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: .1em;
    display: block;
    margin-bottom: .3rem;
}
.field-input {
    width: 100%;
    background: #060c10;
    border: 1px solid #1e2d3d;
    border-radius: 4px;
    color: var(--cyan);
    font-family: var(--font-mono);
    font-size: .92rem;
    padding: .55rem .8rem;
    outline: none;
    transition: border-color .2s;
}
.field-input:focus { border-color: var(--cyan); }
.field-input::placeholder { color: var(--text-dim); }
.field-hint {
    font-family: var(--font-mono);
    font-size: .6rem;
    color: var(--text-dim);
    margin-top: .3rem;
}

/* Botón */
.btn-save {
    width: 100%;
    background: rgba(0, 212, 255, .12);
    color: var(--cyan);
    border: 1px solid rgba(0, 212, 255, .4);
    border-radius: 6px;
    font-family: var(--font-mono);
    font-size: .85rem;
    text-transform: uppercase;
    letter-spacing: .1em;
    padding: .75rem;
    cursor: pointer;
    transition: background .2s, border-color .2s;
    margin-top: 1.8rem;
}
.btn-save:hover { background: rgba(0, 212, 255, .22); border-color: var(--cyan); }
.btn-save:active { background: rgba(0, 212, 255, .32); }

/* Mensaje */
.msg-box {
    display: none;
    font-family: var(--font-mono);
    font-size: .75rem;
    padding: .5rem .9rem;
    border-radius: 4px;
    border: 1px solid;
    margin-top: 1rem;
}
.msg-box.ok  { color: var(--green); border-color: var(--green); background: rgba(0,255,159,.06); }
.msg-box.err { color: var(--red);   border-color: var(--red);   background: rgba(255,69,96,.06); }

/* JSON preview */
.json-preview {
    background: #060c10;
    border: 1px solid #1e2d3d;
    border-radius: 4px;
    font-family: var(--font-mono);
    font-size: .72rem;
    color: #7a9ab5;
    padding: .8rem 1rem;
    margin-top: 1.5rem;
    white-space: pre;
    min-height: 80px;
}
.json-key   { color: var(--amber); }
.json-str   { color: var(--green); }
.json-label {
    font-family: var(--font-mono);
    font-size: .6rem;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: .1em;
    margin-top: 1.4rem;
    margin-bottom: .3rem;
}
</style>
</head>
<body>

<header class="ctrl-header">
  <div class="ctrl-header-inner">
    <a href="mmdvm.php" style="background:#1a2535;color:var(--cyan);border:1px solid rgba(0,212,255,.3);font-family:var(--font-mono);font-size:.75rem;padding:.35rem .9rem;border-radius:4px;text-decoration:none;">← Panel PHPPLUS</a>
    <span style="font-family:var(--font-orb);color:var(--cyan);font-size:1.1rem;letter-spacing:.1em;">INFO MÁQUINA</span>
  </div>
</header>

<div class="card-panel">

  <div class="card-title">▸ Datos de la Raspberry Pi</div>

  <div style="display:flex;flex-direction:column;gap:1.2rem;">

    <div>
      <label class="field-label" for="fIp">Dirección IP</label>
      <input class="field-input" type="text" id="fIp"
             placeholder="192.168.1.126" maxlength="39"
             autocomplete="off" spellcheck="false">
      <div class="field-hint">IPv4 o IPv6 de la Raspberry Pi en la red local</div>
    </div>

    <div>
      <label class="field-label" for="fNombre">Nombre de la máquina</label>
      <input class="field-input" type="text" id="fNombre"
             placeholder="RaspberryPi-MMDVM" maxlength="80"
             autocomplete="off" spellcheck="false">
      <div class="field-hint">Nombre descriptivo, p.ej: RPi4-ADER, OrangePi-DMR…</div>
    </div>

  </div>

  <button class="btn-save" onclick="guardar()">💾 Guardar</button>

  <div class="msg-box" id="msgBox"></div>

  <div class="json-label">▸ info_maquina.json</div>
  <div class="json-preview" id="jsonPreview">Cargando…</div>

</div>

<script>
const FILE = 'info_maquina.php';

function syntaxHL(obj) {
    const s = JSON.stringify(obj, null, 2);
    return s.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*")\s*(:?)/g, (m, str, _q, colon) => {
        if (colon) return `<span class="json-key">${str}</span>:`;
        return `<span class="json-str">${str}</span>`;
    });
}

function showMsg(text, ok) {
    const el = document.getElementById('msgBox');
    el.textContent = (ok ? '✔ ' : '✖ ') + text;
    el.className = 'msg-box ' + (ok ? 'ok' : 'err');
    el.style.display = 'block';
    if (ok) setTimeout(() => el.style.display = 'none', 3000);
}

async function cargar() {
    try {
        const r = await fetch(`${FILE}?action=read`);
        const d = await r.json();
        if (d.ok && d.data) {
            document.getElementById('fIp').value     = d.data.ip     || '';
            document.getElementById('fNombre').value = d.data.nombre || '';
            document.getElementById('jsonPreview').innerHTML = syntaxHL(d.data);
        }
    } catch (e) {
        document.getElementById('jsonPreview').textContent = 'Error al leer el fichero';
    }
}

async function guardar() {
    const ip     = document.getElementById('fIp').value.trim();
    const nombre = document.getElementById('fNombre').value.trim();

    if (!ip && !nombre) { showMsg('Rellena al menos un campo', false); return; }

    try {
        const r = await fetch(`${FILE}?action=save`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ip, nombre }),
        });
        const d = await r.json();
        showMsg(d.msg, d.ok);
        if (d.ok) cargar();
    } catch (e) {
        showMsg('Error de red', false);
    }
}

// Enter guarda
document.addEventListener('keydown', e => { if (e.key === 'Enter') guardar(); });

cargar();
</script>
</body>
</html>
