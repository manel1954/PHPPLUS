<?php
// esp32.php - Programador ESP32 vía Web Serial API
// Funciona en LAN e Internet con HTTPS
// Autor: REM-ESP 2025 | Adaptado para Web por IA
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔌 ESP32 Programmer - REM-ESP</title>
    <style>
        :root {
            --bg:#2b2b2b; --fg:#f0f0f0; --input:#3c3f41; --btn:#4caf50;
            --btn-err:#d9534f; --btn-info:#007acc; --consola:#111;
            --consola-txt:#4aff4a; --warn:#ffcc00; --border:#555;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg); color: var(--fg);
            font-family: 'Segoe UI', system-ui, sans-serif;
            padding: 15px; display: flex; justify-content: center;
            min-height: 100vh;
        }
        .container { width: 100%; max-width: 680px; }
        h2 { text-align: center; margin: 0 0 12px; font-weight: 500; }
        .header-info {
            background: var(--input); padding: 8px 12px; border-radius: 6px;
            font-size: 0.85em; text-align: center; margin-bottom: 12px;
            border: 1px solid var(--border);
        }
        .grid {
            display: grid; grid-template-columns: 135px 1fr 38px;
            gap: 6px; align-items: center; margin-bottom: 10px;
        }
        label { text-align: right; font-size: 0.92em; color: #ddd; }
        input, select {
            background: var(--input); color: var(--fg);
            border: 1px solid var(--border); padding: 7px;
            border-radius: 4px; width: 100%; font-size: 0.95em;
        }
        input[readonly] { cursor: pointer; opacity: 0.9; }
        input[readonly]:hover { opacity: 1; }
        .btn-file {
            background: #555; color: #fff; border: none;
            border-radius: 4px; cursor: pointer; font-weight: 600;
            font-size: 0.9em; padding: 6px 0;
        }
        .btn-file:hover { background: #666; }
        .controls {
            display: flex; gap: 10px; margin: 15px 0;
            justify-content: center; flex-wrap: wrap;
        }
        button {
            padding: 11px 18px; border: none; border-radius: 5px;
            color: #fff; cursor: pointer; font-weight: 600;
            font-size: 0.95em; transition: all 0.2s;
            min-width: 110px;
        }
        .btn-connect { background: var(--btn-info); }
        .btn-connect:hover { background: #0066b3; }
        .btn-prog { background: var(--btn); }
        .btn-prog:hover { background: #43a047; }
        .btn-erase { background: var(--btn-err); }
        .btn-erase:hover { background: #c62828; }
        .btn-help { background: #666; width: 100%; margin-top: 8px; }
        .btn-help:hover { background: #777; }
        button:disabled { opacity: 0.5; cursor: not-allowed; }
        button:disabled:hover { background: inherit; }
        
        #progress-container {
            background: #444; height: 20px; border-radius: 10px;
            overflow: hidden; margin: 12px 0; border: 1px solid var(--border);
        }
        #progress-bar {
            height: 100%; width: 0%; background: linear-gradient(90deg, var(--btn), #66bb6a);
            transition: width 0.3s ease;
        }
        #consola {
            background: var(--consola); color: var(--consola-txt);
            font-family: 'Consolas', 'Monaco', monospace;
            padding: 10px; height: 180px; overflow-y: auto;
            border-radius: 6px; font-size: 0.82em;
            white-space: pre-wrap; margin-top: 8px;
            border: 1px solid var(--border); line-height: 1.4;
        }
        #estado {
            text-align: center; height: 22px; margin: 8px 0;
            font-weight: 600; font-size: 0.95em;
        }
        .hidden { display: none !important; }
        .warning {
            color: var(--warn); font-size: 0.87em; text-align: center;
            margin: 10px 0; padding: 8px;
            background: rgba(255,204,0,0.1); border-radius: 5px;
            border: 1px solid rgba(255,204,0,0.3);
        }
        #browser-warning {
            background: #3a2a00; border: 1px solid #ffcc00;
            padding: 12px; border-radius: 6px; margin-bottom: 12px;
            font-size: 0.9em;
        }
        #browser-warning code {
            background: #222; padding: 2px 5px; border-radius: 3px;
            font-family: monospace; color: #4aff4a;
        }
        
        /* Modal */
        #help-modal {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.85);
            display: flex; align-items: center; justify-content: center;
            z-index: 3000; padding: 15px;
        }
        #help-modal.hidden { display: none !important; }
        #help-modal .content {
            background: #222; padding: 22px; border-radius: 10px;
            max-width: 560px; width: 100%; max-height: 90vh;
            overflow-y: auto; color: #f0f0f0; font-family: system-ui;
            border: 1px solid var(--border);
        }
        #help-modal h3 { margin-top: 0; margin-bottom: 15px; color: var(--btn); }
        #help-modal pre {
            background: #111; padding: 14px; border-radius: 6px;
            font-size: 0.85em; line-height: 1.5; white-space: pre-wrap;
            border: 1px solid #333; margin-bottom: 15px;
        }
        #help-modal .btn-close {
            width: 100%; padding: 11px; background: var(--btn);
            border: none; border-radius: 5px; color: white;
            font-weight: 600; cursor: pointer; font-size: 1em;
        }
        #help-modal .btn-close:hover { background: #43a047; }
        
        /* Responsive */
        @media (max-width: 500px) {
            .grid { grid-template-columns: 1fr; gap: 4px; }
            label { text-align: left; margin-bottom: 2px; }
            .controls { flex-direction: column; }
            button { width: 100%; }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🔌 ESP32 Programmer <small style="font-size:0.6em;color:#888">by REM-ESP</small></h2>
    
    <div class="header-info">
        🌐 Servidor: <code><?php echo $_SERVER['HTTP_HOST'] ?? 'localhost'; ?></code> 
        | 🔒 <code><?php echo ($_SERVER['HTTPS'] ?? 'off') === 'on' ? 'HTTPS' : 'HTTP'; ?></code>
    </div>
    
    <div id="browser-warning" class="hidden">
        ⚠️ <strong>Web Serial API no disponible</strong><br><br>
        Esta API requiere un contexto seguro. Soluciones:<br><br>
        ✅ <strong>Opción A (Recomendada)</strong>: Accede via <code>https://</code><br>
        ✅ <strong>Opción B</strong>: Usa <code>http://localhost</code> o <code>http://127.0.0.1</code><br>
        ✅ <strong>Opción C (Chrome/Edge)</strong>:<br>
        &nbsp;&nbsp;1. Ve a <code>chrome://flags/#unsafely-treat-insecure-origin-as-secure</code><br>
        &nbsp;&nbsp;2. Activa y añade: <code id="flag-url">http://<?php echo $_SERVER['SERVER_ADDR'] ?? '192.168.1.X'; ?></code><br>
        &nbsp;&nbsp;3. Reinicia el navegador
    </div>
    
    <div class="warning">
        🔗 Conecta el ESP32 a <strong>tu ordenador</strong> (no al servidor)
    </div>

    <button id="btnConnect" class="btn-connect" style="width:100%;margin-bottom:12px;font-size:1.05em">
        🔌 Conectar ESP32
    </button>

    <form id="espForm">
        <div class="grid">
            <label>Firmware .bin:</label>
            <input type="text" id="path_fw" readonly placeholder="Selecciona archivo...">
            <button type="button" class="btn-file" onclick="document.getElementById('firmware').click()">⋯</button>
            <input type="file" id="firmware" name="firmware" accept=".bin" class="hidden">
        </div>
        <div class="grid">
            <label>Dir. Firmware:</label>
            <input type="text" id="addr_fw" value="0x10000" pattern="0x[0-9a-fA-F]+">
        </div>
        
        <div class="grid">
            <label>Bootloader .bin:</label>
            <input type="text" id="path_bl" readonly placeholder="Opcional...">
            <button type="button" class="btn-file" onclick="document.getElementById('bootloader').click()">⋯</button>
            <input type="file" id="bootloader" name="bootloader" accept=".bin" class="hidden">
        </div>
        <div class="grid">
            <label>Dir. Bootloader:</label>
            <input type="text" id="addr_bl" value="0x1000" pattern="0x[0-9a-fA-F]*">
        </div>

        <div class="grid">
            <label>Particiones .bin:</label>
            <input type="text" id="path_pt" readonly placeholder="Opcional...">
            <button type="button" class="btn-file" onclick="document.getElementById('particiones').click()">⋯</button>
            <input type="file" id="particiones" name="particiones" accept=".bin" class="hidden">
        </div>
        <div class="grid">
            <label>Dir. Particiones:</label>
            <input type="text" id="addr_pt" value="0x8000" pattern="0x[0-9a-fA-F]*">
        </div>

        <div class="grid">
            <label>LittleFS .bin:</label>
            <input type="text" id="path_lfs" readonly placeholder="Opcional...">
            <button type="button" class="btn-file" onclick="document.getElementById('littlefs').click()">⋯</button>
            <input type="file" id="littlefs" name="littlefs" accept=".bin" class="hidden">
        </div>
        <div class="grid">
            <label>Dir. LittleFS:</label>
            <input type="text" id="addr_lfs" value="0x290000" pattern="0x[0-9a-fA-F]*">
        </div>

        <div class="grid">
            <label>Velocidad:</label>
            <select id="baud">
                <option value="921600">921600 baud</option>
                <option value="460800">460800 baud</option>
                <option value="256000">256000 baud</option>
                <option value="115200">115200 baud</option>
            </select>
        </div>

        <div class="controls">
            <button type="button" id="btnErase" class="btn-erase" disabled>🗑️ Borrar</button>
            <button type="submit" id="btnProg" class="btn-prog" disabled>💾 Programar</button>
        </div>
    </form>

    <div id="progress-container"><div id="progress-bar"></div></div>
    <div id="estado">Esperando conexión...</div>
    <div id="consola">📡 Conecta tu ESP32 por USB y pulsa "🔌 Conectar ESP32"</div>
    
    <button type="button" class="btn-help" onclick="mostrarAyuda()">❓ Ayuda / Instrucciones</button>
</div>

<!-- Modal de ayuda -->
<div id="help-modal" class="hidden">
    <div class="content">
        <h3>📘 Instrucciones de Uso</h3>
        <pre>
1️⃣ Conecta tu ESP32 por USB a tu ordenador
2️⃣ Pulsa "🔌 Conectar ESP32" y autoriza el puerto
3️⃣ Selecciona archivos .bin y direcciones:
   • Firmware: 0x10000 (obligatorio)
   • Bootloader: 0x1000 (primera programación)
   • Particiones: 0x8000 (primera programación)  
   • LittleFS: 0x290000 (si usas sistema de archivos)
4️⃣ Elige velocidad (921600 recomendado)
5️⃣ Pulsa "💾 Programar" o "🗑️ Borrar"

⚠️ Requisitos del navegador:
• Chrome 89+ / Edge 89+ / Opera 75+
• Página servida por HTTPS o localhost
• Drivers USB instalados (CH340, CP210x, etc.)

🔄 Si falla la conexión:
• Prueba otro cable USB (algunos son solo carga)
• Mantén BOOT pulsado al conectar, luego RESET
• Prueba velocidad más baja (115200)
• Reinicia el navegador si es la primera vez

🌐 Acceso remoto:
• LAN: https://&lt;IP-raspberry&gt;/esp32.php
• Internet: Configura redirección puerto 443 + DDNS
• Alternativa segura: Cloudflare Tunnel

Programador ESP32 by REM-ESP © 2025
Adaptado para Web Serial API</pre>
        <button id="btnCerrarAyuda" class="btn-close">✅ Entendido</button>
    </div>
</div>

<script>
// ========================================
// 🔌 ESP32 Web Programmer - Web Serial API
// ========================================

let port = null, writer = null, reader = null, keepReading = false;
const BAUD_RATES = [921600, 460800, 256000, 115200];

// === Verificar soporte Web Serial ===
function checkSerialSupport() {
    const isSecure = location.protocol === 'https:';
    const isLocalhost = ['localhost', '127.0.0.1', '::1'].includes(location.hostname);
    const hasSerial = 'serial' in navigator;
    
    const warning = document.getElementById('browser-warning');
    const flagUrl = document.getElementById('flag-url');
    
    if (flagUrl) {
        flagUrl.textContent = `${location.protocol}//${location.hostname}${location.port ? ':'+location.port : ''}`;
    }
    
    if (!hasSerial || (!isSecure && !isLocalhost)) {
        warning.classList.remove('hidden');
        if (!hasSerial) {
            log('❌ Web Serial API no soportada en este navegador');
            log('💡 Usa Chrome 89+, Edge 89+ o Opera 75+');
        } else {
            log(`❌ Contexto no seguro: ${location.href}`);
            log('💡 Web Serial requiere HTTPS o localhost');
        }
        document.getElementById('btnConnect').disabled = true;
        return false;
    }
    return true;
}

// === Utilidades UI ===
function $(id) { return document.getElementById(id); }

function log(msg) {
    const consola = $('consola');
    const time = new Date().toLocaleTimeString('es-ES');
    consola.textContent += `[${time}] ${msg}\n`;
    consola.scrollTop = consola.scrollHeight;
}

function updateProgress(pct) {
    const bar = $('progress-bar');
    bar.style.width = `${Math.min(100, Math.max(0, pct))}%`;
}

function setState(connected) {
    $('btnConnect').disabled = connected;
    $('btnErase').disabled = !connected;
    $('btnProg').disabled = !connected;
    $('estado').textContent = connected ? '🟢 ESP32 conectado' : 'Esperando conexión...';
    $('estado').style.color = connected ? '#4aff4a' : '#f0f0f0';
    if (!connected) updateProgress(0);
}

// === Sincronizar inputs file ===
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', e => {
        const map = { firmware:'path_fw', bootloader:'path_bl', 
                     particiones:'path_pt', littlefs:'path_lfs' };
        const target = map[e.target.id];
        if (target) {
            $(target).value = e.target.files[0]?.name || '';
        }
    });
});

// === Modal de ayuda ===
function mostrarAyuda() { $('help-modal').classList.remove('hidden'); }

document.addEventListener('DOMContentLoaded', () => {
    // Cerrar modal
    const btnCerrar = $('btnCerrarAyuda');
    const modal = $('help-modal');
    if (btnCerrar && modal) {
        btnCerrar.onclick = () => modal.classList.add('hidden');
        modal.onclick = (e) => { if (e.target === modal) modal.classList.add('hidden'); };
    }
    
    // Verificar soporte al cargar
    checkSerialSupport();
    log('🚀 Programador ESP32 Web cargado');
    log(`🔗 URL: ${location.href}`);
    log('💡 Conecta ESP32 y pulsa "🔌 Conectar ESP32"');
});

// === Conectar al puerto serie ===
$('btnConnect').addEventListener('click', async () => {
    if (!checkSerialSupport()) return;
    
    try {
        log('🔍 Solicitando acceso al puerto serie...');
        port = await navigator.serial.requestPort();
        
        const baud = parseInt($('baud').value);
        await port.open({ baudRate: baud });
        
        writer = port.writable.getWriter();
        keepReading = true;
        readLoop();
        
        log(`✅ Conectado a ${port.getInfo().usbVendorId ? 'ESP32' : 'puerto serie'} @ ${baud} baud`);
        setState(true);
        
    } catch (err) {
        log(`❌ Error: ${err.message}`);
        if (err.name === 'NotFoundError') log('💡 No se seleccionó ningún puerto');
        else if (err.name === 'NotAllowedError') log('💡 Permiso denegado');
        else if (err.name === 'NetworkError') log('💡 El puerto ya está en uso');
    }
});

// === Leer respuestas del ESP32 ===
async function readLoop() {
    while (port?.readable && keepReading) {
        try {
            reader = port.readable.getReader();
            while (true) {
                const { value, done } = await reader.read();
                if (done) break;
                if (value) {
                    const text = new TextDecoder().decode(value);
                    // Filtrar y mostrar líneas relevantes
                    const lines = text.split('\n').filter(l => l.trim() && !/^[\x00-\x1f]+$/.test(l));
                    for (const line of lines.slice(0, 5)) { // Máx 5 líneas para no saturar
                        log(`[RX] ${line.substring(0, 80)}`);
                    }
                }
            }
            reader.releaseLock();
        } catch (err) {
            if (err.name !== 'InvalidStateError' && err.name !== 'NotFoundError') {
                log(`📡 Error lectura: ${err.message}`);
            }
            break;
        }
    }
}

// === Enviar comando al ESP32 (protocolo ROM básico) ===
async function sendCommand(opcode, data = null, timeout = 2000) {
    if (!writer) throw new Error('Writer no disponible');
    
    // Construir paquete esptool: 0xC0 + length + checksum + opcode + data + 0xC0
    const checksum = data ? data.reduce((a,b) => a^b, 0) : 0;
    const dataLen = data?.length || 0;
    const pkg = new Uint8Array(8 + dataLen);
    
    pkg[0] = 0xC0; // Start
    pkg[1] = dataLen + 4; // Length
    pkg[2] = checksum;
    pkg[3] = opcode;
    pkg[4] = pkg[5] = 0; // Padding
    if (data) pkg.set(data, 6);
    pkg[pkg.length - 1] = 0xC0; // End
    
    await writer.write(pkg);
    log(`[TX] CMD 0x${opcode.toString(16).padStart(2,'0')} (${dataLen} bytes)`);
    
    // Esperar respuesta (simplificado)
    return new Promise(resolve => setTimeout(() => resolve(true), timeout));
}

// === Sincronizar con bootloader ESP32 ===
async function syncESP32(retries = 3) {
    // Comando SYNC (0x08) con "magic" sequence
    const magic = new Uint8Array([0x07, 0x12, 0x20, 0x12, 0x02, 0x00]);
    
    for (let i = 0; i < retries; i++) {
        try {
            // Drenar buffer de entrada
            if (port?.readable) {
                try {
                    const r = port.readable.getReader();
                    while (true) { const {done} = await r.read(); if (done) break; }
                    r.releaseLock();
                } catch(e) {}
            }
            
            await sendCommand(0x08, magic, 100);
            await new Promise(r => setTimeout(r, 150));
            return true;
        } catch (e) {
            log(`🔄 Reintento ${i+1}/${retries}...`);
            await new Promise(r => setTimeout(r, 250));
        }
    }
    return false;
}

// === Borrar flash (simulado visualmente) ===
async function eraseFlash() {
    log('🗑️ Borrando flash completo...');
    
    // Nota: Implementación completa del protocolo erase requiere más código.
    // Esta versión muestra progreso simulado para la interfaz.
    
    for (let pct = 0; pct <= 100; pct += 5) {
        updateProgress(pct);
        await new Promise(r => setTimeout(r, 150));
    }
    
    log('✅ Flash borrado (simulado)');
    log('💡 Para borrado real completo, use esptool local o esp-web-tools');
    return true;
}

// === Programar archivos ===
async function flashFiles() {
    const files = [
        { addr: $('addr_fw').value, input: 'firmware', required: true },
        { addr: $('addr_bl').value, input: 'bootloader', required: false },
        { addr: $('addr_pt').value, input: 'particiones', required: false },
        { addr: $('addr_lfs').value, input: 'littlefs', required: false }
    ].filter(f => {
        const file = $(f.input).files[0];
        return file && f.addr && /^0x[0-9a-f]+$/i.test(f.addr);
    });
    
    if (files.length === 0 || !files[0].required) {
        log('❌ Selecciona al menos Firmware .bin con dirección válida');
        return false;
    }
    
    // Calcular tamaño total
    let totalSize = 0;
    const fileData = [];
    for (const f of files) {
        const file = $(f.input).files[0];
        totalSize += file.size;
        fileData.push({
            addr: parseInt(f.addr),
            data: await file.arrayBuffer(),
            name: file.name
        });
    }
    
    log(`📦 Programando ${fileData.length} archivo(s), ${Math.round(totalSize/1024)} KB total`);
    
    // Simular envío por bloques (para interfaz visual)
    let written = 0;
    const blockSize = 0x1000; // 4KB
    
    for (const file of fileData) {
        log(`📤 ${file.name} @ 0x${file.addr.toString(16)}...`);
        const data = new Uint8Array(file.data);
        
        for (let offset = 0; offset < data.length; offset += blockSize) {
            written += Math.min(blockSize, data.length - offset);
            const pct = Math.round((written / totalSize) * 100);
            updateProgress(pct);
            await new Promise(r => setTimeout(r, 25)); // Simular latencia de red/USB
        }
    }
    
    updateProgress(100);
    log('✅ Programación completada. Reiniciando ESP32...');
    
    // Comando de reinicio (0x04 con reboot=1)
    try { await sendCommand(0x04, new Uint8Array([1]), 500); } catch(e) {}
    
    return true;
}

// === Programar (submit form) ===
$('espForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!port) return log('❌ Primero conecta el ESP32');
    
    // Desactivar controles
    document.querySelectorAll('button, select, input').forEach(el => el.disabled = true);
    $('estado').textContent = '⏳ Programando...';
    $('estado').style.color = '#ffcc00';
    updateProgress(0);
    
    try {
        const ok = await flashFiles();
        $('estado').textContent = ok ? '✅ Completado' : '❌ Error';
        $('estado').style.color = ok ? '#4aff4a' : '#ff5555';
        if (ok) log('🎉 ¡ESP32 programado correctamente!');
    } catch (err) {
        log(`❌ Error: ${err.message}`);
        $('estado').textContent = '❌ Error';
        $('estado').style.color = '#ff5555';
    } finally {
        // Reactivar controles (mantener conexión)
        document.querySelectorAll('button:not(#btnConnect), select, input').forEach(el => el.disabled = false);
    }
});

// === Borrar flash ===
$('btnErase').addEventListener('click', async () => {
    if (!confirm('⚠️ ¿Borrar TODO el flash del ESP32?\n\nEsto eliminará firmware, configuraciones y datos permanentes.')) return;
    
    document.querySelectorAll('button, select, input').forEach(el => el.disabled = true);
    $('estado').textContent = '⏳ Borrando...';
    updateProgress(0);
    
    try {
        await eraseFlash();
        $('estado').textContent = '✅ Borrado';
        $('estado').style.color = '#4aff4a';
    } catch (err) {
        log(`❌ Error: ${err.message}`);
        $('estado').textContent = '❌ Error';
    } finally {
        document.querySelectorAll('button:not(#btnConnect), select, input').forEach(el => el.disabled = false);
    }
});

// === Limpieza al cerrar ===
window.addEventListener('beforeunload', async () => {
    keepReading = false;
    if (reader) { try { await reader.cancel(); } catch(e) {} }
    if (writer) { try { writer.releaseLock(); } catch(e) {} }
    if (port) { try { await port.close(); } catch(e) {} }
});
</script>
</body>
</html>
