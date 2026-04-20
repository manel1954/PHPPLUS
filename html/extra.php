<?php
require_once __DIR__ . '/auth.php';
header('X-Content-Type-Options: nosniff');
$action = $_GET['action'] ?? '';

if ($action === 'dump1090-start') {
    $script = '/home/pi/A108/ejecutar_dump1090.sh';
    if (!file_exists($script)) {
        header('Content-Type: application/json');
        echo json_encode(['ok'=>false,'error'=>'Script no encontrado']);
        exit;
    }
    $output = shell_exec("sudo bash $script 2>&1");
    header('Content-Type: application/json');
    echo json_encode($output !== null
        ? ['ok'=>true,'output'=>trim($output)]
        : ['ok'=>false,'error'=>'shell_exec devolvió null (sudoers?)']);
    exit;
}

if ($action === 'dump1090-log') {
    header('Content-Type: text/plain');
    $log = '/tmp/dump1090.log';
    echo file_exists($log)
        ? implode('', array_slice(file($log), -80))
        : '(log vacío — dump1090 aún no iniciado)';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>✈ dump1090 · ADS-B</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Rajdhani:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<style>
:root {
    --bg: #0a0e14; --surface: #111720; --border: #1e2d3d;
    --green: #00ff9f; --red: #ff4560; --cyan: #00d4ff;
    --amber: #ffb300; --text: #a8b9cc; --text-dim: #4a5568;
    --font-mono: 'Share Tech Mono', monospace;
    --font-ui: 'Rajdhani', sans-serif;
    --font-orb: 'Orbitron', monospace;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--bg); color: var(--text); font-family: var(--font-ui); height: 100vh; display: flex; flex-direction: column; overflow: hidden; }

/* ── Header ── */
.ex-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: .7rem 1.4rem; background: var(--surface);
    border-bottom: 1px solid var(--border); flex-shrink: 0;
}
.ex-title {
    font-family: var(--font-orb); font-size: 1rem; font-weight: 700;
    color: var(--cyan); letter-spacing: .1em; text-transform: uppercase;
}
.ex-subtitle {
    font-family: var(--font-mono); font-size: .7rem;
    color: var(--text-dim); letter-spacing: .1em; margin-top: .15rem;
}
.ex-btns { display: flex; align-items: center; gap: .6rem; }

/* ── Botones ── */
.btn-ex {
    font-family: var(--font-mono); font-size: .7rem; letter-spacing: .08em;
    text-transform: uppercase; border-radius: 4px;
    padding: .28rem .85rem; cursor: pointer; transition: background .2s;
    border: 1px solid; background: transparent;
}
.btn-cyan  { color: var(--cyan);  border-color: var(--cyan);  }
.btn-cyan:hover  { background: rgba(0,212,255,.1); }
.btn-green { color: var(--green); border-color: var(--green); }
.btn-green:hover { background: rgba(0,255,159,.1); }
.btn-red   { color: var(--red);   border-color: var(--red);   }
.btn-red:hover   { background: rgba(255,69,96,.15); }
.btn-dim   { color: var(--text-dim); border-color: #1e2d3d; }
.btn-active { background: rgba(0,212,255,.15) !important; border-color: var(--cyan) !important; color: var(--cyan) !important; }

/* ── Status bar ── */
.ex-status {
    display: flex; align-items: center; gap: 1rem;
    padding: .4rem 1.4rem; background: rgba(0,0,0,.3);
    border-bottom: 1px solid var(--border); flex-shrink: 0;
    font-family: var(--font-mono); font-size: .72rem;
}
.dot-status { width: 8px; height: 8px; border-radius: 50%; background: var(--text-dim); display: inline-block; margin-right: .4rem; transition: background .4s, box-shadow .4s; }
.dot-status.on  { background: var(--green); box-shadow: 0 0 6px var(--green); animation: pulse 2s infinite; }
.dot-status.err { background: var(--red);   box-shadow: 0 0 6px var(--red); }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

/* ── Tabs ── */
.ex-tabs {
    display: flex; gap: .4rem; padding: .6rem 1.4rem;
    background: var(--surface); border-bottom: 1px solid var(--border); flex-shrink: 0;
}

/* ── Contenido ── */
.ex-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.tab-pane { flex: 1; display: none; flex-direction: column; overflow: hidden; }
.tab-pane.active { display: flex; }

/* ── Terminal ── */
#dump1090Out {
    flex: 1; margin: 0; padding: 1rem 1.4rem;
    font-family: var(--font-mono); font-size: .78rem;
    color: var(--green); background: #060c10;
    overflow-y: auto; white-space: pre-wrap; word-break: break-all;
    line-height: 1.55;
}
#dump1090Out::-webkit-scrollbar { width: 4px; }
#dump1090Out::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

/* ── Mapa ── */
#dump1090MapFrame { width: 100%; height: 100%; border: none; background: #000; flex: 1; }

/* ── Launch card ── */
.launch-card {
    margin: 2rem auto; background: var(--surface);
    border: 1px solid var(--border); border-radius: 8px;
    padding: 2rem 2.5rem; max-width: 480px; text-align: center;
}
.launch-icon { font-size: 3rem; margin-bottom: 1rem; }
.launch-title { font-family: var(--font-orb); font-size: 1.1rem; color: var(--cyan); letter-spacing: .08em; margin-bottom: .6rem; }
.launch-desc { font-family: var(--font-mono); font-size: .75rem; color: var(--text-dim); line-height: 1.6; margin-bottom: 1.5rem; }
.launch-params { text-align: left; background: #060c10; border: 1px solid var(--border); border-radius: 4px; padding: .8rem 1rem; margin-bottom: 1.5rem; font-family: var(--font-mono); font-size: .72rem; color: var(--amber); line-height: 1.7; }
</style>
</head>
<body>

<!-- Header -->
<header class="ex-header">
    <div>
        <div class="ex-title">✈ dump1090 · ADS-B Receiver</div>
        <div class="ex-subtitle">SDR · Decodificador de tráfico aéreo</div>
    </div>
    <div class="ex-btns">
        <button id="btnLanzar" class="btn-ex btn-cyan" onclick="lanzarDump1090()">▶ Lanzar dump1090</button>
        <button class="btn-ex btn-green" onclick="fetchDump1090Log()">⟳ Refrescar log</button>
        <button class="btn-ex btn-red" onclick="window.close()">✖ Cerrar</button>
    </div>
</header>

<!-- Status -->
<div class="ex-status">
    <span><span class="dot-status" id="dotStatus"></span><span id="statusTxt">Sin iniciar</span></span>
    <span style="color:var(--border)">|</span>
    <span style="color:var(--text-dim)">Log: <span id="logPath" style="color:var(--amber)">/tmp/dump1090.log</span></span>
    <span style="color:var(--border)">|</span>
    <span style="color:var(--text-dim)">Mapa: <span id="mapUrl" style="color:var(--cyan)">—</span></span>
</div>

<!-- Tabs -->
<div class="ex-tabs">
    <button id="tabBtnLog" class="btn-ex btn-active" onclick="switchTab('log')">📋 Log</button>
    <button id="tabBtnMap" class="btn-ex btn-dim"    onclick="switchTab('map')">🗺 Mapa en vivo</button>
</div>

<!-- Contenido -->
<div class="ex-content">

    <!-- Tab Log -->
    <div id="paneLog" class="tab-pane active">
        <!-- Card inicial -->
        <div id="launchCard" class="launch-card">
            <div class="launch-icon">✈</div>
            <div class="launch-title">dump1090 · ADS-B</div>
            <div class="launch-desc">Pulsa el botón para lanzar dump1090 y empezar a recibir tráfico aéreo en tiempo real.</div>
            <div class="launch-params">
                📄 Config: /home/pi/status.ini<br>
                📝 Log:    /tmp/dump1090.log<br>
                🌐 Mapa:   puerto HTTP (status.ini línea 46)
            </div>
            <button class="btn-ex btn-cyan" style="width:100%;padding:.5rem;font-size:.85rem;" onclick="lanzarDump1090()">▶ Lanzar dump1090</button>
        </div>
        <pre id="dump1090Out" style="display:none;"></pre>
    </div>

    <!-- Tab Mapa -->
    <div id="paneMap" class="tab-pane">
        <iframe id="dump1090MapFrame" src=""></iframe>
    </div>

</div>

<script>
let pollInterval = null;
let dumpsStarted = false;

// Detecta hostname y puerto http desde status.ini (lo leemos via log action al inicio)
const mapHost = window.location.hostname;
const mapPort = 8080; // puerto por defecto; dump1090 usa el de status.ini línea 46
const mapUrl  = 'http://' + mapHost + ':' + mapPort;
document.getElementById('mapUrl').textContent = mapUrl;

function setStatus(state, txt) {
    const dot = document.getElementById('dotStatus');
    dot.className = 'dot-status ' + (state === 'on' ? 'on' : state === 'err' ? 'err' : '');
    document.getElementById('statusTxt').textContent = txt;
}

function switchTab(tab) {
    const isLog = tab === 'log';
    document.getElementById('paneLog').classList.toggle('active', isLog);
    document.getElementById('paneMap').classList.toggle('active', !isLog);
    document.getElementById('tabBtnLog').className = 'btn-ex ' + (isLog  ? 'btn-active' : 'btn-dim');
    document.getElementById('tabBtnMap').className = 'btn-ex ' + (!isLog ? 'btn-active' : 'btn-dim');

    // Carga el iframe solo cuando se abre la pestaña mapa
    if (!isLog) {
        const frame = document.getElementById('dump1090MapFrame');
        if (!frame.src || frame.src === 'about:blank') frame.src = mapUrl;
    }
}

async function lanzarDump1090() {
    const btn = document.getElementById('btnLanzar');
    btn.textContent = '⏳ Iniciando…';
    btn.disabled = true;
    setStatus('', 'Lanzando dump1090…');

    document.getElementById('launchCard').style.display = 'none';
    const out = document.getElementById('dump1090Out');
    out.style.display = 'block';
    out.textContent = '⏳ Ejecutando script…\n';

    try {
        const r = await fetch('?action=dump1090-start');
        const d = await r.json();

        if (d.ok) {
            out.textContent += '✅ ' + (d.output || 'dump1090 iniciado correctamente') + '\n\n--- LOG EN TIEMPO REAL ---\n';
            setStatus('on', 'dump1090 activo · PID en /tmp/dump1090.pid');
            dumpsStarted = true;
            startPoll();
            btn.textContent = '⟳ Relanzar';
            btn.disabled = false;
        } else {
            out.textContent += '❌ Error: ' + d.error + '\n';
            setStatus('err', 'Error al iniciar dump1090');
            btn.textContent = '▶ Reintentar';
            btn.disabled = false;
        }
    } catch (e) {
        out.textContent += '❌ Error de red: ' + e + '\n';
        setStatus('err', 'Error de red');
        btn.textContent = '▶ Reintentar';
        btn.disabled = false;
    }
}

function startPoll() {
    stopPoll();
    pollInterval = setInterval(fetchDump1090Log, 1500);
}

function stopPoll() {
    clearInterval(pollInterval);
    pollInterval = null;
}

function fetchDump1090Log() {
    fetch('?action=dump1090-log&t=' + Date.now())
        .then(r => r.text())
        .then(text => {
            const out = document.getElementById('dump1090Out');
            out.style.display = 'block';
            document.getElementById('launchCard').style.display = 'none';
            out.textContent = text;
            out.scrollTop = out.scrollHeight;
        });
}

// Al cerrar la pestaña, para el polling
window.addEventListener('beforeunload', stopPoll);
</script>
</body>
</html>