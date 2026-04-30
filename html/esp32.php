<?php
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ESP32 Web Flash Tool</title>
<style>
:root{ --primary:#2196F3; --success:#4CAF50; --error:#f44336; --warning:#FF9800; --bg:#1e1e2e; --card:#2a2a3e; --text:#e0e0e0; --mono:'Fira Code', monospace; }
*{ margin:0; padding:0; box-sizing:border-box; }
body{ background:var(--bg); color:var(--text); font-family:system-ui,sans-serif; max-width:1100px; margin:auto; padding:20px; }
header{ text-align:center; margin-bottom:25px; } header h1{ margin-bottom:8px; }
.card{ background:var(--card); border-radius:12px; padding:20px; margin-bottom:20px; }
.card h3{ margin-bottom:15px; color:var(--primary); }
.status{ background:#333; padding:14px; border-radius:8px; margin-bottom:15px; font-family:var(--mono); }
.btn-group{ display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
button, select{ border:none; border-radius:8px; padding:10px 16px; cursor:pointer; font-weight:600; }
button{ background:var(--primary); color:white; } button:hover{ opacity:.9; } button:disabled{ opacity:.5; cursor:not-allowed; }
.success{ background:var(--success); } .danger{ background:var(--error); } .warning{ background:var(--warning); color:black; }
.partition{ display:grid; grid-template-columns:140px 1fr 120px 120px; gap:10px; align-items:center; background:#252538; padding:12px; border-radius:8px; margin-bottom:10px; }
.offset{ color:var(--warning); font-family:var(--mono); } .filesize{ color:#999; text-align:right; font-size:.85rem; }
.progress-wrapper{ margin-top:18px; } .progress-bar{ width:100%; height:30px; background:#333; border-radius:8px; overflow:hidden; position:relative; }
.progress-fill{ height:100%; width:0%; background:linear-gradient(90deg,#4CAF50,#66BB6A); transition:width .2s; display:flex; align-items:center; justify-content:center; color:white; font-weight:bold; }
.progress-label{ margin-top:8px; color:#bbb; font-size:.9rem; }
#log{ background:#111; border-radius:8px; padding:15px; font-family:var(--mono); font-size:.85rem; max-height:420px; overflow-y:auto; white-space:pre-wrap; }
.timestamp{ color:#888; } .hidden{ display:none; } footer{ margin-top:30px; text-align:center; color:#777; font-size:.85rem; }
@media(max-width:700px){ .partition{ grid-template-columns:1fr; } .btn-group{ flex-direction:column; } button, select{ width:100%; } }
</style>
</head>
<body>
<header><h1>ESP32 Web Flash Tool</h1><p>Programador ESP32 desde navegador</p></header>
<div class="card">
    <div id="status" class="status">Esperando conexión...</div>
    <div class="btn-group">
        <button id="connectBtn">🔌 Conectar</button>
        <button id="disconnectBtn" class="danger hidden">❌ Desconectar</button>
        <button id="eraseBtn" class="warning">🗑️ Borrar Flash</button>
        <button id="flashBtn" class="success" disabled>🚀 Programar</button>
        <select id="baudRate">
            <option value="115200">115200</option>
            <option value="230400">230400</option>
            <option value="460800" selected>460800 ⚡</option>
            <option value="921600">921600 🚀</option>
        </select>
    </div>
    <div id="progressContainer" class="progress-wrapper hidden">
        <div class="progress-bar"><div id="progressFill" class="progress-fill">0%</div></div>
        <div id="progressLabel" class="progress-label">Esperando...</div>
    </div>
</div>

<div class="card">
    <h3>Archivos BIN</h3>
    <div class="partition" data-offset="0x1000"><label>Bootloader</label><input type="file" class="binFile" data-type="bootloader" accept=".bin"><span class="offset">0x1000</span><span class="filesize" id="size-bootloader"></span></div>
    <div class="partition" data-offset="0x8000"><label>Partitions</label><input type="file" class="binFile" data-type="partitions" accept=".bin"><span class="offset">0x8000</span><span class="filesize" id="size-partitions"></span></div>
    <div class="partition" data-offset="0x10000"><label>Firmware</label><input type="file" class="binFile" data-type="firmware" accept=".bin"><span class="offset">0x10000</span><span class="filesize" id="size-firmware"></span></div>
    <div class="partition" data-offset="0x290000"><label>LittleFS</label><input type="file" class="binFile" data-type="littlefs" accept=".bin"><span class="offset">0x290000</span><span class="filesize" id="size-littlefs"></span></div>
</div>

<div class="card"><h3>Consola</h3><div id="log"></div></div>
<footer>Chrome / Edge + localhost o HTTPS</footer>

<script type="module">
import { ESPLoader, Transport } from 'https://unpkg.com/esptool-js@0.6.0/bundle.js';

const logDiv = document.getElementById('log');
function log(text, type=''){
    const line = document.createElement('div');
    line.innerHTML = `<span class="timestamp">[${new Date().toLocaleTimeString()}]</span> <span class="${type}">${text}</span>`;
    logDiv.appendChild(line);
    logDiv.scrollTop = logDiv.scrollHeight;
}

const statusEl = document.getElementById('status');
function setStatus(text){ statusEl.textContent = text; }

const progressContainer = document.getElementById('progressContainer');
const progressFill = document.getElementById('progressFill');
const progressLabel = document.getElementById('progressLabel');

function setProgress(percent, text=''){
    progressContainer.classList.remove('hidden');
    progressFill.style.width = percent + '%';
    progressFill.textContent = percent + '%';
    progressLabel.textContent = text;
}

let port = null, transport = null, loader = null;
const selectedFiles = {};

const connectBtn = document.getElementById('connectBtn');
const disconnectBtn = document.getElementById('disconnectBtn');
const eraseBtn = document.getElementById('eraseBtn');
const flashBtn = document.getElementById('flashBtn');
const baudRateSelect = document.getElementById('baudRate');

function updateFlashButton(){
    const hasFiles = Object.keys(selectedFiles).length > 0;
    flashBtn.disabled = !(loader && hasFiles);
}

function parseOffset(offset){
    if (typeof offset === 'number') return offset;
    offset = offset.trim().toLowerCase();
    return offset.startsWith('0x') ? parseInt(offset, 16) : parseInt(offset, 10);
}

function formatBytes(bytes){
    if(bytes < 1024) return bytes + ' B';
    if(bytes < 1024*1024) return (bytes/1024).toFixed(1) + ' KB';
    return (bytes/(1024*1024)).toFixed(1) + ' MB';
}

// 🔌 CONEXIÓN
async function connectESP32(){
    try{
        if(!('serial' in navigator)){ alert('WebSerial no soportado'); return; }
        const baudRate = parseInt(baudRateSelect.value);
        port = await navigator.serial.requestPort();
        
        transport = new Transport(port);
        loader = new ESPLoader({
            transport: transport,
            baudrate: baudRate,
            terminal: { clean(){}, writeLine(data){ log(data); }, write(data){ log(data); } }
        });
        
        await loader.main(); 
        
        log('ESP32 conectado y listo', 'success');
        setStatus('Conectado');
        connectBtn.classList.add('hidden');
        disconnectBtn.classList.remove('hidden');
        updateFlashButton();
    } catch(error){
        log(`ERROR: ${error.message}`, 'error');
    }
}

async function disconnectESP32(){
    try{ if(loader) await loader.disconnect(); } catch(e){}
    loader = null; transport = null; port = null;
    connectBtn.classList.remove('hidden');
    disconnectBtn.classList.add('hidden');
    flashBtn.disabled = true;
    setStatus('Desconectado');
    log('Puerto cerrado');
}

async function eraseFlash(){
    if(!loader){ log('Conecta primero el ESP32', 'error'); return; }
    if(!confirm('¿Seguro que quieres borrar TODO el flash?')) return;
    try{
        setProgress(0, 'Borrando flash...');
        log('Borrando flash completo (esto puede tardar)...');
        await loader.eraseFlash(); 
        setProgress(100, 'Flash borrado');
        log('Flash borrado correctamente', 'success');
    } catch(error){
        log(`ERROR: ${error.message}`, 'error');
    }
}

async function flashESP32() {
    if (!loader) return;
    try {
        const fileKeys = Object.keys(selectedFiles);
        if (fileKeys.length === 0) return;

        const files = fileKeys
            .map(key => selectedFiles[key])
            .sort((a, b) => parseOffset(a.offset) - parseOffset(b.offset));

        setProgress(0, 'Iniciando...');
        log(`Grabando ${files.length} archivo(s)...`);

        for (let i = 0; i < files.length; i++) {
            const item = files[i];
            const address = parseOffset(item.offset);
            const buffer = await item.file.arrayBuffer();
            const uint8Data = new Uint8Array(buffer);

            log(`Escribiendo ${item.file.name} en 0x${address.toString(16)}...`);

            // Usamos la configuración más estándar de la librería
            // Sin pausas artificiales que corten el flujo de datos
            await loader.writeFlash({
                fileArray: [{
                    data: uint8Data,
                    address: address,
                    name: item.file.name
                }],
                flashSize: 'keep',
                flashMode: 'keep',
                flashFreq: 'keep',
                compress: true, 
                reportProgress: (fileIndex, written, total) => {
                    const p = Math.floor((written / total) * 100);
                    setProgress(p, `${item.file.name}: ${p}%`);
                }
            });

            log(`✔ ${item.file.name} OK.`);
        }

        setProgress(100, 'Éxito');
        log(`¡GRABACIÓN COMPLETADA! Se procesaron ${files.length} archivos.`, 'success');

        // Reset
        await transport.setDTR(false);
        await new Promise(r => setTimeout(r, 200));
        await transport.setDTR(true);

    } catch (error) {
        log(`FALLO: ${error.message}`, 'error');
        // Si hay error de sincronización, lo mejor es sugerir un reset físico
        if (error.message.includes('0x78') || error.message.includes('stopped')) {
            log("CONSEJO: Desconecta y reconecta el ESP32, a veces el puerto se queda bloqueado.", "warning");
        }
    }
}

document.querySelectorAll('.binFile').forEach(input => {
    input.addEventListener('change', event => {
        const file = event.target.files[0];
        if(!file) return;
        const row = event.target.closest('.partition');
        const offset = row.dataset.offset;
        const type = event.target.dataset.type;
        selectedFiles[type] = { file, offset };
        const sizeEl = document.getElementById('size-' + type);
        if(sizeEl) sizeEl.textContent = formatBytes(file.size);
        log(`Archivo cargado: ${file.name} para 0x${parseOffset(offset).toString(16)}`);
        updateFlashButton();
    });
});

connectBtn.onclick = connectESP32;
disconnectBtn.onclick = disconnectESP32;
eraseBtn.onclick = eraseFlash;
flashBtn.onclick = flashESP32;

log('ESP32 Web Flash Tool listo');
</script>
</body>
</html>
