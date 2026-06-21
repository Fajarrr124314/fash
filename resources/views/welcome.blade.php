<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Point Of Purchase Fashion YKR</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Narrow:ital,wght@0,400..700;1,400..700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Vite Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        
        /* Custom print overrides */
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 min-h-screen antialiased">
    <!-- Render based on authentication session -->
    @if(session()->has('authenticated'))
        <livewire:pop-builder />
    @else
        <livewire:login />
    @endif
    
    @livewireScripts
</body>
</html>
