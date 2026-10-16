<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir | Portal PMB WIGA</title>
</head>
<body>
    <main>
        <h1>Dashboard Kasir</h1>
        <p>Selamat datang, {{ auth()->user()->name }}.</p>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit">Keluar</button>
        </form>
    </main>
</body>
</html>
