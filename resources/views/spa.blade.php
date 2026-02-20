<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bitacora Operativa</title>
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('bitacora.theme');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else if (theme === 'light') {
                    document.documentElement.classList.remove('dark');
                }

                var density = localStorage.getItem('bitacora.density');
                var densityMap = {
                    compacta: 'density-compact',
                    normal: 'density-normal',
                    grande: 'density-grande'
                };
                if (density && densityMap[density]) {
                    document.documentElement.classList.add(densityMap[density]);
                    document.body.classList.add(densityMap[density]);
                }
            } catch (e) {
                // ignore
            }
        })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div id="app"></div>
</body>
</html>
