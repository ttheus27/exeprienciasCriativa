<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-t">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina : 'Meu Site Padrão'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-light rounded mb-4 px-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Mural</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPrincipal"
                aria-controls="navbarPrincipal" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarPrincipal">

                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="notificacoes.php">Notificações</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="vincular_perfil.php">Vincular Perfil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ver_perfil.php">Ver Perfil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_tags.php">Gerenciar Tags</a>
                    </li>
                </ul>

                <ul class="navbar-nav mb-2 mb-lg-0 me-0">
                    <li class="nav-item d-none d-lg-flex align-items-center me-4">
                        <div class="h-100 d-flex align-items-center me-4">Bem-vindo,
                            <?php echo htmlspecialchars($_SESSION['username']); ?>!
                        </div>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="../mensagem/editar_perfil.php">Editar conta</a></li>
                    <li class="nav-item"><a class="nav-link" href="../auth/logout.php">Sair</a></li>
                </ul>
            </div>
        </div>
    </nav>