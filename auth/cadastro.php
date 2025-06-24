<?php
session_start();
?>
<?php
$titulo_pagina = 'Cadastro';
include '../includes/normal-header.php';
?>
<div class="auth-container">
    <h2>Cadastro de Novo Usuário</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="message error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="processa_cadastro.php" method="post">
        <div class="form-group">
            <label for="username">Usuário:</label>
            <input type="text" id="username" name="username" required
                value="<?= htmlspecialchars($_SESSION['form_data']['username'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required
                value="<?= htmlspecialchars($_SESSION['form_data']['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="password">Senha (mínimo 8 caracteres):</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirmar Senha:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 p-2">Cadastrar</button>
    </form>
    <p>Já tem uma conta? <a href="login.php">Faça login aqui</a>.</p>
</div>
<?php unset($_SESSION['form_data']); // Limpa os dados do formulário da sessão após uso ?>
<?php include '../includes/footer.php'; ?>