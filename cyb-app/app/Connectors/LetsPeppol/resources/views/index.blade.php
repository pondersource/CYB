<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
    </head>
    <body class="antialiased">
        <h1>Let's Peppol</h1>
        <p>Welcome to Let's Peppol, Peppol but free!</p>
        <a href="{{ route('connector.lets_peppol.admin-panel') }}">Go to admin panel</a>
    </body>
</html>
