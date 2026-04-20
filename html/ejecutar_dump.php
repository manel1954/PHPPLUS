<?php
header('Content-Type: application/json');

$script = '/home/pi/A108/ejecutar_dump1090.sh';

if (!file_exists($script)) {
    echo json_encode(['ok' => false, 'error' => 'Script no encontrado: ' . $script]);
    exit;
}
if (!is_executable($script)) {
    echo json_encode(['ok' => false, 'error' => 'Script sin permiso de ejecución']);
    exit;
}

$output = shell_exec("sudo bash $script 2>&1");

if ($output === null) {
    echo json_encode(['ok' => false, 'error' => 'shell_exec devolvió null (sudoers o shell_exec deshabilitado)']);
    exit;
}

echo json_encode(['ok' => true, 'output' => $output]);



<!-- Panel terminal -->
<div id="terminal-box" style="display:none; margin-top:20px;">
    <div style="background:#111; color:#00ff00; font-family:'Courier New',monospace;
                font-size:12px; padding:10px; border:1px solid #00ff00;
                height:300px; overflow-y:auto; white-space:pre-wrap;"
         id="terminal-output">
    </div>
    <div style="margin-top:5px; display:flex; gap:10px;">
        <button onclick="stopPolling()" 
                style="background:#ff4444; color:white; border:none; padding:5px 12px; cursor:pointer;">
            ⏹ Parar refresco
        </button>
        <button onclick="startPolling()" 
                style="background:#00aa00; color:white; border:none; padding:5px 12px; cursor:pointer;">
            ▶ Reanudar
        </button>
        <button onclick="clearTerminal()" 
                style="background:#555; color:white; border:none; padding:5px 12px; cursor:pointer;">
            🗑 Limpiar
        </button>
    </div>
</div>

<script>
let pollInterval = null;

function ejecutarDump() {
    fetch('/ejecutar_dump.php')
        .then(r => r.text())
        .then(raw => {
            const data = JSON.parse(raw);
            if (data.ok) {
                document.getElementById('terminal-box').style.display = 'block';
                startPolling();
            } else {
                alert('❌ Error: ' + data.error);
            }
        })
        .catch(err => alert('❌ Error: ' + err));
}

function startPolling() {
    if (pollInterval) return;
    pollInterval = setInterval(fetchLog, 1500);
    fetchLog();
}

function stopPolling() {
    clearInterval(pollInterval);
    pollInterval = null;
}

function clearTerminal() {
    document.getElementById('terminal-output').textContent = '';
}

function fetchLog() {
    fetch('/log_dump.php?t=' + Date.now())
        .then(r => r.text())
        .then(text => {
            const term = document.getElementById('terminal-output');
            term.textContent = text;
            term.scrollTop = term.scrollHeight; // auto-scroll al final
        });
}
</script>