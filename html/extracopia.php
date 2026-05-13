<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MENU EXTRA</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        .navbar-granate {
            background-color: #6b0f1a;
            min-height: 60px;
            padding-top: 0;
            padding-bottom: 0;
        }

        .navbar-granate .nav-link {
            color: #fff;
            font-size: 0.85rem;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .navbar-granate .nav-link:hover {
            color: #ffcccc;
        }

        .navbar-granate .navbar-brand {
            padding-top: 0;
            padding-bottom: 0;
        }

        .navbar-granate .navbar-brand img {
            height: 45px;
        }

        .card {
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: scale(1.02);
        }
    </style>
</head>

<body class="bg-dark text-white">

<!-- HEADER -->
<nav class="navbar navbar-expand-md navbar-granate">
    <div class="container">
        <a class="navbar-brand" href="#">
          <img src="Logo_ea3eiz.png" alt="Logo">
        </a>
<div class="station-divider" style="height:50px;"></div>
    <div style="flex:1;display:flex;flex-direction:row;align-items:center;justify-content:center;gap:.4rem;"><?php
            $model = '';
            if (file_exists('/proc/device-tree/model'))
                $model = trim(str_replace("\0", '', file_get_contents('/proc/device-tree/model')));
            if ($model === '') $model = trim(shell_exec('uname -m 2>/dev/null') ?? '');
            $ml = strtolower($model);
            $icon = str_contains($ml, 'raspberry') ? '🍓' : (str_contains($ml, 'orange') ? '🍊' : '🖥️');
            $modelShort = str_contains($ml, 'raspberry') ? substr($model, 0, 14) : $model;
        ?>
        <span id="siModelLabel" style="font-size:2.8rem;line-height:1;"><?php echo $icon; ?></span>
        <!-- <span class="station-meta-value" id="siModel" style="color:var(--violet);font-size:.7rem;white-space:nowrap;"><?php echo htmlspecialchars($modelShort); ?></span> -->
    </div>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>

        <a href="mmdvm.php" class="btn btn-outline-light btn-sm">
            <i class="bi bi-house-fill me-1"></i> Panel PHPPLUS
        </a>
    </div>
</nav>
<br><br>
<!-- CONTENIDO -->

    <div class="row g-3 justify-content-start">

        <!-- DUMP1090 CONTROL -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-secondary border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <i class="bi bi-airplane-fill me-2" style="color: #00ff15;"></i>Dump1090 Control
                    </h5>
                    <p class="card-text text-white-50 small flex-grow-1">
                        Lanzador configurador Dump1090
                    </p>
                    <a href="/dump1090.php" target="_blank" class="btn btn-info btn-sm mt-2 text-dark fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                    </a>
                </div>
            </div>
        </div>

        <!-- DUMP1090 MONITOR -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-secondary border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <i class="bi bi-airplane-fill me-2" style="color: #00ff15;"></i>Dump1090 Monitor
                    </h5>
                    <p class="card-text text-white-50 small flex-grow-1">
                        Seguimiento de aeronaves en tiempo real
                    </p>
                    <a href="/dump1090monitor.php" target="_blank" class="btn btn-info btn-sm mt-2 text-dark fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                    </a>
                </div>
            </div>
        </div>

        <!-- AMBE SERVER -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-secondary border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <i class="bi bi-cpu-fill me-2" style="color:#ff4dff;"></i>AMBE SERVER
                    </h5>
                    <p class="card-text text-white-50 small flex-grow-1">
                        Servidor AMBE · Control de voz digital DMR
                    </p>
                    <a href="/ambeserver.php" target="_blank" class="btn btn-info btn-sm mt-2 text-dark fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                    </a>
                </div>
            </div>
        </div>

        <!-- RADARBOX -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-secondary border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <i class="bi bi-airplane-engines-fill me-2" style="color:#ff6600;"></i>RADARBOX
                    </h5>
                    <p class="card-text text-white-50 small flex-grow-1">
                        Feeder Radarbox · Tracking ADS-B global.
                    </p>
                    <a href="/radarbox.php" target="_blank" class="btn btn-info btn-sm mt-2 text-dark fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                    </a>
                </div>
            </div>
        </div>

        <!-- FLIGHTRADAR24 -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-secondary border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <i class="bi bi-airplane-engines-fill me-2" style="color:#ffcc00;"></i>FLIGHTRADAR24
                    </h5>
                    <p class="card-text text-white-50 small flex-grow-1">
                        Feeder FR24 · Seguimiento de vuelos en tiempo real.
                    </p>
                    <a href="/flightradar.php" target="_blank" class="btn btn-info btn-sm mt-2 text-dark fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                    </a>
                </div>
            </div>
        </div>

        <!-- RADIOSONDE (AUTO_RX) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-secondary border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <i class="bi bi-balloon-fill me-2" style="color:#66ffcc;"></i>RADIOSONDE
                    </h5>
                    <p class="card-text text-white-50 small flex-grow-1">
                        Seguimiento de sondas meteorológicas en tiempo real.
                    </p>
                    <a href="/auto_rx.php" target="_blank" class="btn btn-info btn-sm mt-2 text-dark fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                    </a>
                </div>
            </div>
        </div>

        <!-- SVXLINK -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-secondary border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <i class="bi bi-broadcast me-2" style="color:#00d4ff;"></i>SVXLINK
                    </h5>
                    <p class="card-text text-white-50 small flex-grow-1">
                        Control de repetidor · EchoLink · Configuración y logs
                    </p>
                    <a href="/svxlink.php" target="_blank" class="btn btn-info btn-sm mt-2 text-dark fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                    </a>
                </div>
            </div>
        </div>

        <!-- BLUETOOTH -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-secondary border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <i class="bi bi-bluetooth me-2" style="color:#00d4ff;"></i>Bluetooth
                    </h5>
                    <p class="card-text text-white-50 small flex-grow-1">
                        Gestión de dispositivos Bluetooth
                    </p>
                    <a href="/bluetooth.php" target="_blank" class="btn btn-info btn-sm mt-2 text-dark fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                    </a>
                </div>
            </div>
        </div>

        <!-- PROGRAMADOR ESP32 -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-secondary border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <i class="bi bi-cpu me-2" style="color:#00ffff;"></i>Programador ESP32
                    </h5>
                    <p class="card-text text-white-50 small flex-grow-1">
                        Grabador de Firmware para módulos ESP32 vía WebSerial
                    </p>
                    <a href="/esp32.php" target="_blank" class="btn btn-info btn-sm mt-2 text-dark fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                    </a>
                </div>
            </div>
        </div>

<!-- FUSION 2X -->
<!-- <div class="col-12 col-sm-6 col-lg-3">
    <div class="card bg-secondary border-0 h-100">
        <div class="card-body d-flex flex-column">
            <h5 class="card-title">
                <i class="bi bi-broadcast-pin me-2" style="color:#ff3b3b;"></i>Fusion 2X
            </h5>
            <p class="card-text text-white-50 small flex-grow-1">
                Servidor Fusion 2X · Interfaz web en tiempo real para equipos Yaesu
            </p>
            <a href="/fusion2x.php" target="_blank" class="btn btn-info btn-sm mt-2 text-dark fw-bold">
                <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
            </a>
        </div>
    </div>
</div> -->

<!-- OPENWEBRX -->
<div class="col-12 col-sm-6 col-lg-3">
    <div class="card bg-secondary border-0 h-100">
        <div class="card-body d-flex flex-column">

            <h5 class="card-title">
                <i class="bi bi-broadcast me-2" style="color:#00ff99;"></i>OpenWebRX
            </h5>

            <p class="card-text text-white-50 small flex-grow-1">
                Receptor SDR en tiempo real · Web interface para RTL-SDR y decodificación digital.
            </p>

            <a href="/openwebrx_control.php" target="_blank"
               class="btn btn-info btn-sm mt-2 text-dark fw-bold">
                <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
            </a>

        </div>
    </div>
</div>
        
 
<!-- MIS ENLACES PREFERIDOS -->
<div class="col-12 col-sm-6 col-lg-3">
    <div class="card bg-secondary border-0 h-100">
        <div class="card-body d-flex flex-column">
            <h5 class="card-title">
                <i class="bi bi-bookmarks-fill me-2" style="color:#00e5ff;"></i>Mis Enlaces Preferidos
            </h5>
            <p class="card-text text-white-50 small flex-grow-1">
                Panel de acceso rápido a enlaces de radioafición y servicios web.
            </p>
            <a href="/mis_enlaces.php" target="_blank" class="btn btn-info btn-sm mt-2 text-dark fw-bold">
                <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
            </a>
        </div>
    </div>
</div>

<!-- LIMPIEZA DEL SISTEMA -->
<div class="col-12 col-sm-6 col-lg-3">
    <div class="card bg-secondary border-0 h-100">
        <div class="card-body d-flex flex-column">
            <h5 class="card-title">
                <i class="bi bi-trash3-fill me-2" style="color:#ff6666;"></i>
                Limpieza del sistema
            </h5>

            <p class="card-text text-white-50 small flex-grow-1">
                Limpieza de logs, temporales y mantenimiento básico del sistema para liberar espacio.
            </p>

            <a href="/limpieza.php" target="_blank"
               class="btn btn-info btn-sm mt-2 text-dark fw-bold">
                <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
            </a>
        </div>
    </div>
</div>

        <!-- EDITOR GENERAL
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-secondary border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <i class="bi bi-sliders me-2" style="color:#00e5ff;"></i>EDITOR GENERAL
                    </h5>
                    <p class="card-text text-white-50 small flex-grow-1">
                        Configuración global · Callsign · Id · Frecuencias · Posición · URL
                    </p>
                    <a href="/editor_general_config.php" target="_blank" class="btn btn-info btn-sm mt-2 text-dark fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir
                    </a>
                </div>
            </div>
        </div> -->

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

