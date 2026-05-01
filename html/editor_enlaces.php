<?php
// editor_enlaces.php
// Editor CRUD para enlaces.json
// Coloca este archivo en el mismo directorio que enlaces.json y mis_enlaces.php

define('JSON_FILE', __DIR__ . '/enlaces.json');

// ── Funciones de datos ───────────────────────────────────────────
function leerEnlaces(): array {
    if (!file_exists(JSON_FILE)) {
        file_put_contents(JSON_FILE, '[]');
    }
    $raw = file_get_contents(JSON_FILE);
    return json_decode($raw, true) ?? [];
}

function guardarEnlaces(array $enlaces): bool {
    $json = json_encode($enlaces, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents(JSON_FILE, $json) !== false;
}

function responderJSON(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function sanitizarEnlace(array $d): array {
    return [
        'nombre' => trim($d['nombre'] ?? ''),
        'url'    => trim($d['url']    ?? ''),
        'bg'     => preg_match('/^#[0-9a-fA-F]{3,6}$/', $d['bg'] ?? '') ? $d['bg'] : '#333333',
        'fg'     => preg_match('/^#[0-9a-fA-F]{3,6}$/', $d['fg'] ?? '') ? $d['fg'] : '#ffffff',
        'local'  => !empty($d['local']),
    ];
}

// ── API (peticiones AJAX) ────────────────────────────────────────
$action = $_GET['action'] ?? '';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_GET['api']);

if ($isAjax || in_array($action, ['list','save','add','update','delete','reorder'])) {

    $method = $_SERVER['REQUEST_METHOD'];
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];

    switch ($action) {

        // GET ?action=list
        case 'list':
            responderJSON(['ok' => true, 'data' => leerEnlaces()]);

        // POST ?action=add   body: {nombre,url,bg,fg,local}
        case 'add':
            $nuevo = sanitizarEnlace($body);
            if ($nuevo['nombre'] === '') responderJSON(['ok'=>false,'msg'=>'Nombre requerido'], 400);
            $lista = leerEnlaces();
            $lista[] = $nuevo;
            guardarEnlaces($lista);
            responderJSON(['ok'=>true,'msg'=>'Enlace añadido','data'=>$lista]);

        // POST ?action=update  body: {index, nombre,url,bg,fg,local}
        case 'update':
            $idx = (int)($body['index'] ?? -1);
            $lista = leerEnlaces();
            if ($idx < 0 || $idx >= count($lista)) responderJSON(['ok'=>false,'msg'=>'Índice inválido'], 400);
            $lista[$idx] = sanitizarEnlace($body);
            guardarEnlaces($lista);
            responderJSON(['ok'=>true,'msg'=>'Enlace actualizado','data'=>$lista]);

        // POST ?action=delete  body: {index}
        case 'delete':
            $idx = (int)($body['index'] ?? -1);
            $lista = leerEnlaces();
            if ($idx < 0 || $idx >= count($lista)) responderJSON(['ok'=>false,'msg'=>'Índice inválido'], 400);
            $nombre = $lista[$idx]['nombre'];
            array_splice($lista, $idx, 1);
            guardarEnlaces($lista);
            responderJSON(['ok'=>true,'msg'=>"\"$nombre\" eliminado",'data'=>$lista]);

        // POST ?action=reorder  body: {order: [0,3,1,2,...]}
        case 'reorder':
            $order = $body['order'] ?? [];
            $lista = leerEnlaces();
            $nueva = [];
            foreach ($order as $i) {
                if (isset($lista[(int)$i])) $nueva[] = $lista[(int)$i];
            }
            guardarEnlaces($nueva);
            responderJSON(['ok'=>true,'msg'=>'Orden guardado']);

        default:
            responderJSON(['ok'=>false,'msg'=>'Acción desconocida'], 400);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editor de Enlaces · EA3EIZ</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&family=Rajdhani:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --bg:       #181818;
    --surface:  #222;
    --card:     #282828;
    --border:   #383838;
    --text:     #ddd;
    --dim:      #777;
    --cyan:     #00e5ff;
    --amber:    #ffb300;
    --red:      #e53935;
    --green:    #43a047;
    --radius:   5px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{font-size:14px}
body{background:var(--bg);color:var(--text);font-family:'Rajdhani',sans-serif;min-height:100vh}

/* ── HEADER ── */
header{
    background:linear-gradient(135deg,#111 0%,#1a1a1a 100%);
    border-bottom:2px solid #333;
    padding:14px 28px;
    display:flex;align-items:center;gap:14px;
}
header .badge{
    font-family:'Orbitron',sans-serif;font-size:10px;font-weight:700;
    color:var(--cyan);background:rgba(0,229,255,.08);
    border:1px solid rgba(0,229,255,.3);border-radius:3px;
    padding:3px 9px;letter-spacing:2px;
}
header h1{font-family:'Orbitron',sans-serif;font-size:18px;font-weight:900;letter-spacing:5px;color:#fff}
header .sub{margin-left:auto;font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--cyan);opacity:.6;letter-spacing:2px}
.btn-volver{
    margin-left:auto;
    font-family:'Orbitron',sans-serif;font-size:10px;letter-spacing:2px;
    padding:6px 16px;border-radius:var(--radius);border:1px solid #444;
    background:#2a2a2a;color:#aaa;cursor:pointer;text-decoration:none;
    transition:all .2s;
}
.btn-volver:hover{background:#333;color:#fff;border-color:#666}

/* ── LAYOUT ── */
.layout{display:grid;grid-template-columns:1fr 340px;gap:0;height:calc(100vh - 56px)}

/* ── TABLA IZQUIERDA ── */
.panel-lista{
    display:flex;flex-direction:column;overflow:hidden;
    border-right:1px solid var(--border);
}
.toolbar{
    background:var(--surface);border-bottom:1px solid var(--border);
    padding:10px 16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;
}
.toolbar input[type=text]{
    flex:1;min-width:160px;background:#1a1a1a;border:1px solid #444;border-radius:var(--radius);
    color:var(--text);font-family:'Share Tech Mono',monospace;font-size:12px;
    padding:6px 12px;outline:none;transition:border-color .2s;
}
.toolbar input[type=text]:focus{border-color:rgba(0,229,255,.5)}
.btn{
    font-family:'Orbitron',sans-serif;font-size:9px;letter-spacing:1px;
    padding:7px 14px;border-radius:var(--radius);border:none;cursor:pointer;
    transition:all .15s;white-space:nowrap;
}
.btn-add{background:var(--green);color:#fff}
.btn-add:hover{background:#2e7d32}
.btn-danger{background:var(--red);color:#fff}
.btn-danger:hover{background:#b71c1c}
.btn-save{background:var(--cyan);color:#000}
.btn-save:hover{filter:brightness(1.2)}
.btn-secondary{background:#333;color:#aaa;border:1px solid #444}
.btn-secondary:hover{background:#444;color:#fff}

.count-badge{
    font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--dim);
    margin-left:auto;white-space:nowrap;
}

/* ── TABLA ── */
.tabla-wrap{flex:1;overflow-y:auto}
table{width:100%;border-collapse:collapse}
thead th{
    position:sticky;top:0;z-index:10;
    background:#1e1e1e;border-bottom:2px solid var(--border);
    font-family:'Orbitron',sans-serif;font-size:9px;letter-spacing:2px;
    color:var(--dim);padding:10px 10px;text-align:left;font-weight:400;
}
tbody tr{
    border-bottom:1px solid #2a2a2a;cursor:grab;
    transition:background .12s;
}
tbody tr:hover{background:#2e2e2e}
tbody tr.selected{background:#1a3040 !important}
tbody tr.drag-over{border-top:2px solid var(--cyan)}
tbody tr.dragging{opacity:.4}
td{padding:7px 10px;vertical-align:middle}
td.td-color{width:32px}
.color-dot{
    width:22px;height:22px;border-radius:3px;display:inline-block;
    border:1px solid rgba(255,255,255,.15);flex-shrink:0;
}
td.td-nombre{font-family:'Share Tech Mono',monospace;font-size:12px;color:#ccc;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
td.td-url{font-size:11px;color:var(--dim);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
td.td-local{width:40px;text-align:center;font-size:10px;color:var(--amber)}
td.td-drag{width:20px;color:#444;cursor:grab;font-size:16px;text-align:center;padding-left:6px}
td.td-actions{width:70px;text-align:right;padding-right:8px}
.btn-edit,.btn-del{
    font-size:13px;background:none;border:none;cursor:pointer;
    padding:2px 4px;border-radius:3px;transition:all .15s;
}
.btn-edit{color:#5599ff}.btn-edit:hover{background:#1a3060;color:#7ab0ff}
.btn-del{color:#cc4444}.btn-del:hover{background:#3d1111;color:#ff6666}

/* ── PANEL DERECHO ── */
.panel-form{
    background:var(--card);display:flex;flex-direction:column;
    overflow-y:auto;
}
.form-header{
    background:#1e1e1e;border-bottom:1px solid var(--border);
    padding:14px 20px;
    font-family:'Orbitron',sans-serif;font-size:11px;letter-spacing:3px;
    color:var(--cyan);
}
.form-body{padding:20px;display:flex;flex-direction:column;gap:14px;flex:1}
.field{display:flex;flex-direction:column;gap:5px}
.field label{
    font-family:'Orbitron',sans-serif;font-size:9px;letter-spacing:2px;
    color:var(--dim);text-transform:uppercase;
}
.field input[type=text],
.field input[type=url]{
    background:#1a1a1a;border:1px solid #444;border-radius:var(--radius);
    color:var(--text);font-family:'Share Tech Mono',monospace;font-size:13px;
    padding:8px 12px;outline:none;transition:border-color .2s;width:100%;
}
.field input:focus{border-color:rgba(0,229,255,.6)}

.field-colors{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.color-preview{
    height:40px;border-radius:var(--radius);display:flex;align-items:center;
    justify-content:center;font-family:'Share Tech Mono',monospace;font-size:12px;
    border:1px solid rgba(255,255,255,.1);margin-top:4px;transition:all .2s;
    letter-spacing:1px;
}
.color-row{display:flex;align-items:center;gap:8px}
.color-row input[type=color]{
    width:38px;height:34px;border:1px solid #444;border-radius:3px;
    background:#1a1a1a;cursor:pointer;padding:1px;
}
.color-row input[type=text]{flex:1}

.field-local{display:flex;align-items:center;gap:10px}
.field-local input[type=checkbox]{
    width:16px;height:16px;accent-color:var(--amber);cursor:pointer;
}
.field-local label{
    font-family:'Rajdhani',sans-serif;font-size:14px;color:var(--amber);letter-spacing:1px;
    cursor:pointer;
}

.form-actions{display:flex;flex-direction:column;gap:8px;margin-top:4px}
.btn-lg{padding:10px 16px;font-size:10px;letter-spacing:2px;width:100%}

.form-hint{font-size:11px;color:var(--dim);font-family:'Share Tech Mono',monospace;line-height:1.6;padding:12px;background:#1a1a1a;border-radius:var(--radius);border-left:3px solid var(--border)}

/* ── TOAST ── */
#toast{
    position:fixed;bottom:24px;right:24px;z-index:9999;
    background:#222;border:1px solid #444;border-radius:6px;
    font-family:'Share Tech Mono',monospace;font-size:12px;
    padding:10px 20px;color:#ccc;
    transform:translateY(20px);opacity:0;
    transition:all .3s;pointer-events:none;
}
#toast.show{transform:translateY(0);opacity:1}
#toast.ok{border-color:var(--green);color:var(--green)}
#toast.err{border-color:var(--red);color:var(--red)}

/* ── MODAL CONFIRM ── */
.overlay{
    display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);
    z-index:1000;align-items:center;justify-content:center;
}
.overlay.show{display:flex}
.modal{
    background:#222;border:1px solid #444;border-radius:8px;
    padding:28px 32px;max-width:380px;width:90%;text-align:center;
}
.modal h3{font-family:'Orbitron',sans-serif;font-size:14px;color:var(--red);margin-bottom:12px;letter-spacing:2px}
.modal p{font-size:13px;color:#aaa;margin-bottom:22px;line-height:1.6}
.modal-actions{display:flex;gap:10px;justify-content:center}

/* ── SCROLLBAR ── */
::-webkit-scrollbar{width:5px}
::-webkit-scrollbar-track{background:#1a1a1a}
::-webkit-scrollbar-thumb{background:#444;border-radius:3px}
::-webkit-scrollbar-thumb:hover{background:#666}

/* responsive */
@media(max-width:800px){
    .layout{grid-template-columns:1fr;grid-template-rows:1fr auto}
    .panel-form{max-height:50vh}
}
</style>
</head>
<body>

<header>
    <div class="badge">EDITOR</div>
    <h1>ENLACES</h1>
    <a href="mis_enlaces.php" class="btn-volver" target="_blank">↗ VER PANEL</a>
</header>

<div class="layout">

    <!-- ── LISTA ── -->
    <div class="panel-lista">
        <div class="toolbar">
            <input type="text" id="buscar" placeholder="🔍  Filtrar..." oninput="filtrar(this.value)">
            <button class="btn btn-add" onclick="nuevoEnlace()">＋ AÑADIR</button>
            <span class="count-badge" id="countBadge">— enlaces</span>
        </div>
        <div class="tabla-wrap">
            <table id="tabla">
                <thead>
                    <tr>
                        <th style="width:20px"></th>
                        <th style="width:30px">COLOR</th>
                        <th>NOMBRE</th>
                        <th>URL</th>
                        <th style="width:40px">LOCAL</th>
                        <th style="width:70px"></th>
                    </tr>
                </thead>
                <tbody id="tbody"></tbody>
            </table>
        </div>
    </div>

    <!-- ── FORMULARIO ── -->
    <div class="panel-form">
        <div class="form-header" id="formTitle">NUEVO ENLACE</div>
        <div class="form-body">

            <input type="hidden" id="editIndex" value="-1">

            <div class="field">
                <label>Nombre del enlace</label>
                <input type="text" id="fNombre" placeholder="BM Monitor" maxlength="60"
                       oninput="actualizarPreview()">
            </div>

            <div class="field">
                <label>URL</label>
                <input type="text" id="fUrl" placeholder="https://..." maxlength="500"
                       oninput="actualizarPreview()">
            </div>

            <div class="field-colors">
                <div class="field">
                    <label>Color fondo</label>
                    <div class="color-row">
                        <input type="color" id="fBgPicker" value="#1a5490" oninput="syncColor('bg')">
                        <input type="text"  id="fBg"       value="#1a5490" maxlength="7"
                               oninput="syncColorFromText('bg')" placeholder="#1a5490">
                    </div>
                </div>
                <div class="field">
                    <label>Color texto</label>
                    <div class="color-row">
                        <input type="color" id="fFgPicker" value="#ffffff" oninput="syncColor('fg')">
                        <input type="text"  id="fFg"       value="#ffffff" maxlength="7"
                               oninput="syncColorFromText('fg')" placeholder="#ffffff">
                    </div>
                </div>
            </div>

            <div class="color-preview" id="preview">NOMBRE DEL ENLACE</div>

            <div class="field-local">
                <input type="checkbox" id="fLocal" onchange="actualizarPreview()">
                <label for="fLocal">⚠ Enlace local (sin URL web)</label>
            </div>

            <div class="form-actions">
                <button class="btn btn-save btn-lg" onclick="guardarEnlace()">💾 GUARDAR ENLACE</button>
                <button class="btn btn-secondary btn-lg" onclick="limpiarForm()">✕ CANCELAR</button>
            </div>

            <div class="form-hint">
                💡 Arrastra las filas de la tabla para reordenar.<br>
                El cambio de orden se guarda automáticamente.
            </div>

        </div>
    </div>

</div>

<!-- Modal confirmación borrado -->
<div class="overlay" id="overlay">
    <div class="modal">
        <h3>⚠ CONFIRMAR BORRADO</h3>
        <p id="modalMsg">¿Seguro que quieres eliminar este enlace?</p>
        <div class="modal-actions">
            <button class="btn btn-danger" id="btnConfirmDel">ELIMINAR</button>
            <button class="btn btn-secondary" onclick="cerrarModal()">CANCELAR</button>
        </div>
    </div>
</div>

<div id="toast"></div>

<script>
// ── Estado global ─────────────────────────────────────────
let enlaces = [];
let filtro  = '';
let dragSrc = null;

// ── API helpers ───────────────────────────────────────────
async function api(action, body = null) {
    const opts = {
        method: body ? 'POST' : 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }
    };
    if (body) opts.body = JSON.stringify(body);
    const r = await fetch(`?action=${action}&api=1`, opts);
    return r.json();
}

// ── Carga inicial ─────────────────────────────────────────
async function cargar() {
    const res = await api('list');
    if (res.ok) { enlaces = res.data; renderTabla(); }
    else toast('Error al cargar: ' + res.msg, 'err');
}

// ── Render tabla ──────────────────────────────────────────
function renderTabla() {
    const tbody = document.getElementById('tbody');
    const q = filtro.toLowerCase();
    tbody.innerHTML = '';

    let visibles = 0;
    enlaces.forEach((e, i) => {
        if (q && !e.nombre.toLowerCase().includes(q) && !e.url.toLowerCase().includes(q)) return;
        visibles++;
        const tr = document.createElement('tr');
        tr.dataset.index = i;
        tr.draggable = true;
        tr.innerHTML = `
            <td class="td-drag">⠿</td>
            <td class="td-color"><span class="color-dot" style="background:${e.bg}"></span></td>
            <td class="td-nombre" title="${esc(e.nombre)}">${esc(e.nombre)}</td>
            <td class="td-url"   title="${esc(e.url)}">${e.url ? esc(e.url) : '<em style="color:#555">—</em>'}</td>
            <td class="td-local">${e.local ? '⚠' : ''}</td>
            <td class="td-actions">
                <button class="btn-edit" onclick="editarEnlace(${i})" title="Editar">✏</button>
                <button class="btn-del"  onclick="pedirBorrar(${i})" title="Eliminar">🗑</button>
            </td>`;

        // Drag & drop
        tr.addEventListener('dragstart', e => { dragSrc = tr; tr.classList.add('dragging'); });
        tr.addEventListener('dragend',   e => { tr.classList.remove('dragging'); limpiarDragOver(); });
        tr.addEventListener('dragover',  e => { e.preventDefault(); limpiarDragOver(); tr.classList.add('drag-over'); });
        tr.addEventListener('drop',      e => { e.preventDefault(); moverFila(parseInt(dragSrc.dataset.index), parseInt(tr.dataset.index)); });

        tbody.appendChild(tr);
    });

    document.getElementById('countBadge').textContent =
        (q ? `${visibles} de ` : '') + `${enlaces.length} enlaces`;
}

function limpiarDragOver() {
    document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
}

function filtrar(v) { filtro = v; renderTabla(); }

// ── Drag reorder ──────────────────────────────────────────
async function moverFila(desde, hasta) {
    if (desde === hasta) return;
    const item = enlaces.splice(desde, 1)[0];
    enlaces.splice(hasta, 0, item);
    renderTabla();
    // Guardar nuevo orden
    await api('reorder', { order: enlaces.map((_, i) => i) });
    // Re-fetch para que los índices sean correctos
    const res = await api('list');
    if (res.ok) { enlaces = res.data; renderTabla(); }
    toast('Orden guardado', 'ok');
}

// ── Formulario ────────────────────────────────────────────
function nuevoEnlace() {
    document.getElementById('editIndex').value = -1;
    document.getElementById('formTitle').textContent = 'NUEVO ENLACE';
    document.getElementById('fNombre').value = '';
    document.getElementById('fUrl').value    = '';
    document.getElementById('fBg').value     = '#1a5490';
    document.getElementById('fBgPicker').value = '#1a5490';
    document.getElementById('fFg').value     = '#ffffff';
    document.getElementById('fFgPicker').value = '#ffffff';
    document.getElementById('fLocal').checked = false;
    actualizarPreview();
    document.getElementById('fNombre').focus();
}

function editarEnlace(i) {
    const e = enlaces[i];
    document.getElementById('editIndex').value = i;
    document.getElementById('formTitle').textContent = 'EDITAR ENLACE';
    document.getElementById('fNombre').value = e.nombre;
    document.getElementById('fUrl').value    = e.url || '';
    const bg = e.bg || '#333333';
    const fg = e.fg || '#ffffff';
    document.getElementById('fBg').value       = bg;
    document.getElementById('fBgPicker').value = bg;
    document.getElementById('fFg').value       = fg;
    document.getElementById('fFgPicker').value = fg;
    document.getElementById('fLocal').checked  = !!e.local;
    actualizarPreview();
    document.getElementById('fNombre').focus();
}

function limpiarForm() {
    document.getElementById('editIndex').value = -1;
    document.getElementById('formTitle').textContent = 'NUEVO ENLACE';
    ['fNombre','fUrl'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('fBg').value = '#1a5490';
    document.getElementById('fBgPicker').value = '#1a5490';
    document.getElementById('fFg').value = '#ffffff';
    document.getElementById('fFgPicker').value = '#ffffff';
    document.getElementById('fLocal').checked = false;
    actualizarPreview();
}

async function guardarEnlace() {
    const nombre = document.getElementById('fNombre').value.trim();
    if (!nombre) { toast('El nombre es obligatorio', 'err'); document.getElementById('fNombre').focus(); return; }

    const payload = {
        nombre,
        url:   document.getElementById('fUrl').value.trim(),
        bg:    document.getElementById('fBg').value.trim() || '#333333',
        fg:    document.getElementById('fFg').value.trim() || '#ffffff',
        local: document.getElementById('fLocal').checked,
    };

    const idx = parseInt(document.getElementById('editIndex').value);
    let res;
    if (idx >= 0) {
        payload.index = idx;
        res = await api('update', payload);
    } else {
        res = await api('add', payload);
    }

    if (res.ok) {
        enlaces = res.data;
        renderTabla();
        toast(res.msg, 'ok');
        limpiarForm();
    } else {
        toast('Error: ' + res.msg, 'err');
    }
}

// ── Color sync ────────────────────────────────────────────
function syncColor(which) {
    const picker = document.getElementById(which === 'bg' ? 'fBgPicker' : 'fFgPicker');
    const text   = document.getElementById(which === 'bg' ? 'fBg'       : 'fFg');
    text.value   = picker.value;
    actualizarPreview();
}
function syncColorFromText(which) {
    const text   = document.getElementById(which === 'bg' ? 'fBg'       : 'fFg');
    const picker = document.getElementById(which === 'bg' ? 'fBgPicker' : 'fFgPicker');
    if (/^#[0-9a-fA-F]{6}$/.test(text.value)) picker.value = text.value;
    actualizarPreview();
}
function actualizarPreview() {
    const prev = document.getElementById('preview');
    const bg = document.getElementById('fBg').value || '#333';
    const fg = document.getElementById('fFg').value || '#fff';
    const nombre = document.getElementById('fNombre').value || 'NOMBRE DEL ENLACE';
    prev.style.background = bg;
    prev.style.color = fg;
    prev.textContent = nombre;
}

// ── Borrado ───────────────────────────────────────────────
let pendingDelete = -1;

function pedirBorrar(i) {
    pendingDelete = i;
    document.getElementById('modalMsg').textContent =
        `¿Eliminar "${enlaces[i].nombre}"? Esta acción no se puede deshacer.`;
    document.getElementById('overlay').classList.add('show');
    document.getElementById('btnConfirmDel').onclick = confirmarBorrar;
}

async function confirmarBorrar() {
    cerrarModal();
    const res = await api('delete', { index: pendingDelete });
    if (res.ok) {
        enlaces = res.data;
        renderTabla();
        toast(res.msg, 'ok');
        // Si estábamos editando ese elemento, limpiar form
        if (parseInt(document.getElementById('editIndex').value) === pendingDelete) limpiarForm();
    } else {
        toast('Error: ' + res.msg, 'err');
    }
}

function cerrarModal() {
    document.getElementById('overlay').classList.remove('show');
    pendingDelete = -1;
}
document.getElementById('overlay').addEventListener('click', e => { if (e.target === e.currentTarget) cerrarModal(); });

// ── Toast ─────────────────────────────────────────────────
let toastTimer;
function toast(msg, type = '') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'show ' + type;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { el.className = ''; }, 3000);
}

// ── Utils ─────────────────────────────────────────────────
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Init ──────────────────────────────────────────────────
cargar();
actualizarPreview();
</script>
</body>
</html>
