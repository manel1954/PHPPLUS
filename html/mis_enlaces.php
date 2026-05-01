<?php
// mis_enlaces.php - Panel de enlaces rápidos
// Lee los datos de enlaces.json (mismo directorio)

define('JSON_FILE', __DIR__ . '/enlaces.json');

$enlaces = [];
if (file_exists(JSON_FILE)) {
    $raw = file_get_contents(JSON_FILE);
    $enlaces = json_decode($raw, true) ?? [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIS ENLACES · EA3EIZ</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:       #1e1e1e;
            --surface:  #2b2b2b;
            --border:   #3a3a3a;
            --text:     #e0e0e0;
            --dim:      #888;
            --radius:   4px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Share Tech Mono', monospace;
            min-height: 100vh;
            padding: 0 0 40px;
        }

        /* ── Header ── */
        header {
            background: linear-gradient(135deg, #111 0%, #1c1c1c 100%);
            border-bottom: 2px solid #444;
            padding: 22px 40px 18px;
            display: flex;
            align-items: center;
            gap: 18px;
        }
        header .logo-badge {
            font-family: 'Orbitron', sans-serif;
            font-size: 11px;
            font-weight: 700;
            color: #00e5ff;
            background: rgba(0,229,255,.08);
            border: 1px solid rgba(0,229,255,.3);
            border-radius: 3px;
            padding: 4px 10px;
            letter-spacing: 2px;
            white-space: nowrap;
        }
        header h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 6px;
            color: #fff;
        }
        header .callsign {
            font-family: 'Orbitron', sans-serif;
            font-size: 13px;
            color: #00e5ff;
            letter-spacing: 3px;
            opacity: .7;
        }
        .btn-editor {
            margin-left: auto;
            font-family: 'Orbitron', sans-serif;
            font-size: 9px;
            letter-spacing: 2px;
            padding: 6px 14px;
            border-radius: var(--radius);
            border: 1px solid #444;
            background: #2a2a2a;
            color: #888;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
            white-space: nowrap;
        }
        .btn-editor:hover { background: #333; color: #fff; border-color: #00e5ff55; }

        /* ── Search ── */
        .search-wrap {
            padding: 16px 30px 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .search-wrap input {
            flex: 1;
            background: #222;
            border: 1px solid #444;
            border-radius: var(--radius);
            color: #ccc;
            font-family: 'Share Tech Mono', monospace;
            font-size: 13px;
            padding: 7px 14px;
            outline: none;
            transition: border-color .2s;
            max-width: 420px;
        }
        .search-wrap input:focus { border-color: #00e5ff88; }
        .stats { font-size: 11px; color: var(--dim); white-space: nowrap; }

        /* ── Grid ── */
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px 8px;
            padding: 12px 30px;
        }
        .btn-link {
            display: block;
            width: 100%;
            padding: 7px 12px;
            border: none;
            border-radius: var(--radius);
            font-family: 'Share Tech Mono', monospace;
            font-size: 11.5px;
            text-align: center;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            transition: filter .15s, transform .1s, box-shadow .15s;
            position: relative;
        }
        .btn-link:hover {
            filter: brightness(1.35);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,.5);
            z-index: 2;
        }
        .btn-link:active { transform: translateY(0); filter: brightness(1.1); }
        .btn-local { cursor: not-allowed; opacity: .55; }
        .btn-local::after {
            content: '⚠ local';
            position: absolute;
            right: 6px; top: 50%;
            transform: translateY(-50%);
            font-size: 9px; opacity: .7;
        }
        .btn-wrap.hidden { display: none; }

        /* ── Footer ── */
        footer { margin-top: 28px; display: flex; justify-content: center; }
        .btn-salir {
            font-family: 'Orbitron', sans-serif;
            font-size: 12px; letter-spacing: 3px;
            padding: 9px 40px; border-radius: var(--radius);
            border: none; cursor: pointer;
            background: #c0392b; color: white;
            transition: background .2s, transform .1s;
        }
        .btn-salir:hover { background: #27ae60; transform: scale(1.04); }

        @media (max-width: 700px) {
            .grid { grid-template-columns: repeat(2, 1fr); padding: 10px 12px; }
            header h1 { font-size: 16px; letter-spacing: 3px; }
            header { padding: 16px; gap: 10px; }
        }
        @media (max-width: 460px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<header>
    <div class="logo-badge">PANEL</div>
    <h1>MIS ENLACES</h1>
    <span class="callsign">EA3EIZ</span>
    <a href="editor_enlaces.php" class="btn-editor">✏ EDITOR</a>
</header>

<div class="search-wrap">
    <span>🔍</span>
    <input type="text" id="buscar" placeholder="Filtrar enlaces..." autocomplete="off" oninput="filtrar(this.value)">
    <span class="stats" id="contador"></span>
</div>

<div class="grid" id="grid">
<?php if (empty($enlaces)): ?>
    <p style="color:#666;padding:40px;grid-column:1/-1;text-align:center;">
        No hay enlaces.
        <a href="editor_enlaces.php" style="color:#00e5ff">→ Abre el editor para añadir</a>
    </p>
<?php else: ?>
    <?php foreach ($enlaces as $e):
        $nombre = htmlspecialchars($e['nombre'] ?? '');
        $url    = htmlspecialchars($e['url']    ?? '');
        $bg     = htmlspecialchars($e['bg']     ?? '#333');
        $fg     = htmlspecialchars($e['fg']     ?? '#fff');
        $local  = !empty($e['local']);
        $key    = strtolower($e['nombre'] ?? '');
    ?>
        <div class="btn-wrap" data-nombre="<?= htmlspecialchars($key) ?>">
            <?php if ($local || $url === ''): ?>
                <span class="btn-link btn-local"
                      style="background:<?= $bg ?>;color:<?= $fg ?>;"
                      title="Acción local – no disponible en web">
                    <?= $nombre ?>
                </span>
            <?php else: ?>
                <a class="btn-link"
                   href="<?= $url ?>"
                   target="_blank"
                   rel="noopener noreferrer"
                   style="background:<?= $bg ?>;color:<?= $fg ?>;"
                   title="<?= $url ?>">
                    <?= $nombre ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<footer>
    <button class="btn-salir" onclick="window.close()">✕ &nbsp;CERRAR</button>
</footer>

<script>
    const items = document.querySelectorAll('.btn-wrap');
    const total = <?= count($enlaces) ?>;

    function actualizar() {
        let visible = 0;
        items.forEach(el => { if (!el.classList.contains('hidden')) visible++; });
        document.getElementById('contador').textContent =
            (visible < total ? visible + ' de ' : '') + total + ' enlaces';
    }

    function filtrar(texto) {
        const q = texto.toLowerCase().trim();
        items.forEach(el => {
            el.classList.toggle('hidden', q !== '' && !el.dataset.nombre.includes(q));
        });
        actualizar();
    }

    actualizar();
</script>
</body>
</html>
