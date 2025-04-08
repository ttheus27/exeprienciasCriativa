<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Usuário</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar {
            background-color: #34495e;
            width: 200px;
            height: 100vh;
            position: fixed;
            top: 60px;
            left: 0;
            padding: 20px;
            box-sizing: border-box;
            color: white;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            margin-bottom: 10px;
        }

        .main-content {
            margin-left: 200px;
            padding: 20px;
            margin-top: 60px;
        }

        .logo {
            font-size: 1.2rem;
            font-weight: bold;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Painel do Usuário</div>
    <div class="user-info">Bem-vindo, <?php echo htmlspecialchars($email); ?></div>
</header>

<div class="sidebar">
    <a href="#">Dashboard</a>
    <a href="#">Editar Perfil</a>
    <a href="#">Configurações</a>
    <a href="logout.php">Sair</a>
</div>

<div class="main-content">
    <h1>Perfil</h1>
    <p>Este é o seu painel de perfil.</p>
</div>

</body>
</html>
