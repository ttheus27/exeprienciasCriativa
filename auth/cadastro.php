<?php
session_start(); 
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Cadastro</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <div class="auth-container"> 
        <h2>Cadastro de Novo Usuário</h2>

        <?php
        // Exibe mensagens de erro ou sucesso da sessão com as classes CSS
        if (isset($_SESSION['error_message'])) {
            echo '<div class="message error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        // pode adicionar uma mensagem de sucesso aqui se redirecionar de volta com sucesso
        // if (isset($_SESSION['success_message'])) {
        //     echo '<div class="message success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
        //     unset($_SESSION['success_message']);
        // }
        ?>

        <form action="processa_cadastro.php" method="post">
            <div class="form-group"> <!-- Agrupa Label e Input -->
                <label for="username">Usuário:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group"> <!-- Agrupa Label e Input -->
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group"> <!-- Agrupa Label e Input -->
                <label for="confirm_password">Confirmar Senha:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Cadastrar</button>
        </form>
        <p>Já tem uma conta? <a href="login.php">Faça login aqui</a>.</p>
    </div> 
</body>
</html>