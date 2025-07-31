<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Custom Meta --}}
    <meta name="description"
        content="Website Apparel Marketplace yang menyediakan berbagai produk fashion berkualitas. Temukan koleksi terbaru kami dan nikmati pengalaman berbelanja yang menyenangkan.">
    <meta property="og:image" content="{{ asset('storage/promotion_banner.png') }}">


    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    {{-- Icon --}}
    <link rel="icon" href="{{ asset('storage/favicon.png') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
