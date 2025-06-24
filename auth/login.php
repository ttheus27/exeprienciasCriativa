<?php
session_start();
?>
<?php
$titulo_pagina = 'Login';
include '../includes/normal-header.php';
?>
<div class="auth-container">
    <h2>Login</h2>

    <?php
    // Exibe mensagens de erro ou sucesso da sessão com as classes CSS
    if (isset($_SESSION['error_message'])) {
        echo '<div class="message error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    if (isset($_SESSION['success_message'])) {
        echo '<div class="message success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
        unset($_SESSION['success_message']);
    }
    ?>

    <form action="processa_login.php" method="post">
        <div class="form-group">
            <label for="username">Usuário ou email:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 p-2">Entrar</button>
    </form>
    <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se aqui</a>.</p>
</div>
<?php include '../includes/footer.php'; ?>