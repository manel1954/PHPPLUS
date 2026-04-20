<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MENU EXTRA</title>
    <style>
        .btn-header { background: cyan; padding: 10px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1><?php echo "holaç"; ?></h1>
    
    <button class="btn-header" onclick="extraOpen()">⌨ DUMP1090</button>
    
    <script>
    function extraOpen() {
        window.open('/dump1090.php', '_blank');
    }
    </script>
</body>
</html>
