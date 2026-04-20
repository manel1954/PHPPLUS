<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MENU EXTRA</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-deep:    #080b10;
            --bg-panel:   rgba(10, 18, 30, 0.85);
            --accent:     #00e5ff;
            --accent-dim: rgba(0, 229, 255, 0.15);
            --accent-glow:rgba(0, 229, 255, 0.45);
            --green:      #00ff88;
            --amber:      #ffb300;
            --red:        #ff3d5a;
            --border:     rgba(0, 229, 255, 0.2);
            --text:       #c8ddf0;
            --text-dim:   #556677;
            --font-mono:  'Share Tech Mono', monospace;
            --font-hud:   'Orbitron', sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: var(--bg-deep);
            background-image:
                radial-gradient(ellipse 80% 60% at 50% -10%, rgba(0, 229, 255, 0.08) 0%, transparent 60%),
                linear-gradient(180deg, #080b10 0%, #050810 100%);
            min-height: 100vh;
            font-family: var(--font-mono);
            color: var(--text);
            overflow-x: hidden;
        }

        /* ── GRID OVERLAY ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(0,229,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,229,255,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        /* ── RADAR RING ── */
        .radar-ring {
            position: fixed;
            top: -320px; right: -320px;
            width: 700px; height: 700px;
            border-radius: 50%;
            border: 1px solid rgba(0,229,255,0.06);
            pointer-events: none; z-index: 0;
        }
        .radar-ring::before {
            content: '';
            position: absolute;
            inset: 60px;
            border-radius: 50%;
            border: 1px solid rgba(0,229,255,0.05);
        }
        .radar-ring::after {
            content: '';
            position: absolute;
            inset: 130px;
            border-radius: 50%;
            border: 1px solid rgba(0,229,255,0.04);
        }

        /* ── WRAPPER ── */
        .page-wrapper {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── TOP BAR ── */
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 28px;
            border-bottom: 1px solid var(--border);
            background: rgba(5,8,16,0.7);
            backdrop-filter: blur(12px);
        }
        .top-bar-brand {
            font-family: var(--font-hud);
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .2em;
            color: var(--accent);
            text-transform: uppercase;
        }
        .top-bar-status {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .6rem;
            color: var(--text-dim);
            letter-spacing: .1em;
        }
        .dot-live {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 8px var(--green);
            animation: blink 1.6s ease-in-out infinite;
        }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

        /* ── HEADER ── */
        .hero {
            padding: 52px 28px 36px;
            text-align: center;
        }
        .hero-label {
            font-size: .6rem;
            letter-spacing: .35em;
            color: var(--accent);
            text-transform: uppercase;
            margin-bottom: 12px;
            opacity: .8;
        }
        .hero-title {
            font-family: var(--font-hud);
            font-size: clamp(1.8rem, 5vw, 3.2rem);
            font-weight: 900;
            letter-spacing: .08em;
            color: #fff;
            text-shadow: 0 0 40px var(--accent-glow);
            line-height: 1;
        }
        .hero-title span { color: var(--accent); }
        .hero-sub {
            margin-top: 10px;
            font-size: .7rem;
            color: var(--text-dim);
            letter-spacing: .15em;
        }

        /* ── DIVIDER ── */
        .hud-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 28px;
            margin-bottom: 36px;
        }
        .hud-divider::before,
        .hud-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        .hud-divider-icon {
            width: 8px; height: 8px;
            border: 1px solid var(--accent);
            transform: rotate(45deg);
            box-shadow: 0 0 8px var(--accent-glow);
        }

        /* ── CARD GRID ── */
        .cards-grid {
            padding: 0 20px 60px;
        }

        /* ── TOOL CARD ── */
        .tool-card {
            position: relative;
            background: var(--bg-panel);
            border: 1px solid var(--border);
            border-radius: 4px;
            overflow: hidden;
            cursor: pointer;
            transition: border-color .25s, box-shadow .25s, transform .2s;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            padding: 24px 22px 20px;
            height: 100%;
            backdrop-filter: blur(8px);
        }
        .tool-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: var(--card-accent, var(--accent));
            opacity: .6;
            transition: opacity .25s;
        }
        .tool-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 30%, var(--accent-dim) 0%, transparent 60%);
            opacity: 0;
            transition: opacity .3s;
            pointer-events: none;
        }
        .tool-card:hover {
            border-color: var(--card-accent, var(--accent));
            box-shadow: 0 0 32px rgba(0,229,255,0.12), 0 8px 32px rgba(0,0,0,.5);
            transform: translateY(-3px);
        }
        .tool-card:hover::before { opacity: 1; }
        .tool-card:hover::after  { opacity: 1; }

        /* Corner brackets */
        .tool-card .corner {
            position: absolute;
            width: 10px; height: 10px;
            border-color: var(--card-accent, var(--accent));
            border-style: solid;
            opacity: 0;
            transition: opacity .25s;
        }
        .tool-card:hover .corner { opacity: .6; }
        .corner.tl { top: 4px; left: 4px;  border-width: 1px 0 0 1px; }
        .corner.tr { top: 4px; right: 4px; border-width: 1px 1px 0 0; }
        .corner.bl { bottom: 4px; left: 4px;  border-width: 0 0 1px 1px; }
        .corner.br { bottom: 4px; right: 4px; border-width: 0 1px 1px 0; }

        .card-index {
            font-size: .55rem;
            letter-spacing: .2em;
            color: var(--text-dim);
            margin-bottom: 16px;
        }
        .card-icon {
            font-size: 1.9rem;
            color: var(--card-accent, var(--accent));
            margin-bottom: 14px;
            filter: drop-shadow(0 0 10px var(--card-accent, var(--accent)));
            transition: filter .25s;
        }
        .tool-card:hover .card-icon {
            filter: drop-shadow(0 0 18px var(--card-accent, var(--accent)));
        }
        .card-title {
            font-family: var(--font-hud);
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .12em;
            color: #fff;
            margin-bottom: 8px;
        }
        .card-desc {
            font-size: .62rem;
            color: var(--text-dim);
            line-height: 1.6;
            letter-spacing: .05em;
            flex: 1;
        }
        .card-arrow {
            margin-top: 20px;
            font-size: .6rem;
            letter-spacing: .2em;
            color: var(--card-accent, var(--accent));
            display: flex;
            align-items: center;
            gap: 6px;
            opacity: 0;
            transform: translateX(-6px);
            transition: opacity .25s, transform .25s;
        }
        .tool-card:hover .card-arrow {
            opacity: 1;
            transform: translateX(0);
        }
        .card-arrow::after {
            content: '→';
        }

        /* ── FOOTER ── */
        .hud-footer {
            margin-top: auto;
            padding: 14px 28px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: .55rem;
            color: var(--text-dim);
            letter-spacing: .12em;
        }

        /* ── ANIMATIONS ── */
        .fade-in {
            opacity: 0;
            transform: translateY(16px);
            animation: fadeUp .5s ease forwards;
        }
        @keyframes fadeUp {
            to { opacity:1; transform:translateY(0); }
        }
        .delay-1 { animation-delay: .1s; }
        .delay-2 { animation-delay: .18s; }
        .delay-3 { animation-delay: .26s; }
        .delay-4 { animation-delay: .34s; }
        .delay-5 { animation-delay: .42s; }

        @media (max-width: 576px) {
            .top-bar { padding: 12px 16px; }
            .hero     { padding: 36px 16px 24px; }
            .cards-grid { padding: 0 12px 40px; }
        }
    </style>
</head>
<body>

<?php
    $timestamp = date('Y-m-d H:i:s');
    $version   = '2.1.0';
?>

<div class="radar-ring"></div>

<div class="page-wrapper">

    <!-- TOP BAR -->
    <div class="top-bar fade-in">
        <div class="top-bar-brand">EA3EIZ · <?php echo htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'LOCAL'); ?></div>
        <div class="top-bar-status">
            <div class="dot-live"></div>
            SYSTEM ONLINE
        </div>
    </div>

    <!-- HERO -->
    <div class="hero fade-in delay-1">
        <div class="hero-label">Panel de Control · <?php echo htmlspecialchars($timestamp); ?></div>
        <div class="hero-title">MENÚ <span>EXTRA</span></div>
        <div class="hero-sub">Herramientas y servicios adicionales</div>
    </div>

    <!-- DIVIDER -->
    <div class="hud-divider fade-in delay-2">
        <div class="hud-divider-icon"></div>
    </div>

    <!-- CARDS -->
    <div class="cards-grid fade-in delay-3">
        <div class="container-fluid px-0">
            <div class="row g-3 justify-content-center">

                <!-- DUMP1090 -->
                <div class="col-12 col-sm-6 col-lg-4">
                    <a class="tool-card" style="--card-accent:#00e5ff;" href="/dump1090.php" target="_blank">
                        <span class="corner tl"></span><span class="corner tr"></span>
                        <span class="corner bl"></span><span class="corner br"></span>
                        <div class="card-index">01 / AVIACIÓN</div>
                        <div class="card-icon"><i class="bi bi-airplane-fill"></i></div>
                        <div class="card-title">DUMP1090</div>
                        <div class="card-desc">Receptor ADS-B · Seguimiento de aeronaves en tiempo real sobre mapa interactivo.</div>
                        <div class="card-arrow">ABRIR</div>
                    </a>
                </div>

                <!-- Ejemplo: puedes añadir más tarjetas aquí -->
                <!--
                <div class="col-12 col-sm-6 col-lg-4">
                    <a class="tool-card" style="--card-accent:#00ff88;" href="/otra_herramienta.php" target="_blank">
                        <span class="corner tl"></span><span class="corner tr"></span>
                        <span class="corner bl"></span><span class="corner br"></span>
                        <div class="card-index">02 / RADIO</div>
                        <div class="card-icon"><i class="bi bi-broadcast"></i></div>
                        <div class="card-title">NOMBRE</div>
                        <div class="card-desc">Descripción de la herramienta.</div>
                        <div class="card-arrow">ABRIR</div>
                    </a>
                </div>
                -->

            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="hud-footer fade-in delay-5">
        <span>ADER · ASSOCIACIÓ DE RADIOAFICIONATS</span>
        <span>v<?php echo $version; ?></span>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
