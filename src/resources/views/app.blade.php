<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>RemindMe App - WeThrive & Matthew Gordon</title>
    <meta name="csrf-token" content="{{ csrf_token() }}"
    @vite(['resources/js/app.js', 'resources/css/app.css'])

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
</head>
<body class="antialiased">
    <div id="app">
        <router-view></router-view>
    </div>
</body>
</html>
