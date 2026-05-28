<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Roteadores</title>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="text-center">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Cadastro de Roteadores</h1>
        @if (Route::has('login'))
            <div class="space-x-4">
                @auth
                    <a href="{{ url('/SistemaCadastro') }}" class="text-blue-500 underline">SistemaCadastro</a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Entrar</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-gray-600 text-white rounded">Registrar</a>
                    @endif
                @endauth
            </div>
        @endif
    </div>
</body>
</html>
