<?php
// Inicia a sessão ANTES de qualquer output ou require que use sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// É crucial garantir que apenas usuários logados possam ACESSAR a página de criação
require_once '../includes/auth_check.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Criar Mensagem</title>
    <link rel="stylesheet" href="style.css"> <!-- Seu CSS de mensagens -->
    <!-- <link rel="stylesheet" href="../css/style.css"> --> <!-- CSS geral opcional -->
</head>
<body>
    <h1>Nova Mensagem</h1>

     <!-- Exibir mensagens de erro, se houver (ex: se validação falhar no process.php) -->
    <?php
        if (isset($_SESSION['message_error'])) {
            echo '<div class="message error-message" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px;">' . htmlspecialchars($_SESSION['message_error']) . '</div>';
            unset($_SESSION['message_error']);
        }
    ?>

    <form action="process.php" method="POST">
         <div class="form-group"> <!-- Similar ao formulário de login/cadastro -->
             <label for="titulo">Título:</label>
            <input type="text" id="titulo" name="titulo" placeholder="Título" required>
        </div>
         <div class="form-group">
            <label for="conteudo">Conteúdo:</label>
            <textarea id="conteudo" name="conteudo" rows="5" placeholder="Conteúdo da mensagem" required></textarea>
        </div>
        <button type="submit" name="criar">Criar Mensagem</button>
    </form>
    <br>
    <a href="index.php">← Voltar para Mensagens</a>
</body>
</html>