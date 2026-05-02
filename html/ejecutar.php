<?php
// ejecutar.php  –  Ejecuta comandos locales almacenados con prefijo cmd:
// Solo accesible desde localhost por seguridad

header('Content-Type: application/json; charset=utf-8');

// Seguridad: solo desde la red local
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$esLocal = ($ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0);
if (!$esLocal) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'msg' => 'Acceso denegado']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$cmd  = trim($body['cmd'] ?? '');

if ($cmd === '') {
    echo json_encode(['ok' => false, 'msg' => 'Comando vacío']);
    exit;
}

// Eliminar prefijo cmd: si viene con él
if (strpos($cmd, 'cmd:') === 0) {
    $cmd = trim(substr($cmd, 4));
}

// Ejecutar en background (no bloquea el browser)
$cmdFull = 'nohup ' . $cmd . ' > /dev/null 2>&1 &';
exec($cmdFull, $output, $ret);

echo json_encode([
    'ok'  => true,
    'msg' => 'Comando lanzado: ' . htmlspecialchars($cmd),
    'cmd' => $cmd
]);
