<?php
session_start();
require_once '../includes/auth_check.php'; // Garante que apenas usuários logados acessem
require_once '../mensagem/db.php'; // Conexão com o banco

// Buscar dados atuais do usuário para pré-preencher o e-mail (opcional, mas bom)
$user_id = $_SESSION['user_id'];
$current_email = '';

$stmt_user = $conn->prepare("SELECT email FROM usuarios WHERE id = ?");
if ($stmt_user) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows === 1) {
        $user_data = $result_user->fetch_assoc();
        $current_email = $user_data['email'];
    }
    $stmt_user->close();
}
// $conn->close(); // Não feche a conexão aqui se o processamento estiver no mesmo arquivo
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="ExperienciasCriativamensagem/style.css"> <!-- Seu CSS principal -->
</head>
<body>
    <div class="auth-container"> <!-- Reutilizando o container de auth -->
        <h2>Editar Perfil</h2>

        <?php
        // Exibir mensagens de sucesso/erro da sessão
        if (isset($_SESSION['success_message'])) {
            echo '<div class="message success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="message error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <form action="processa_editar_perfil.php" method="post">
            <div class="form-group">
                <label for="email">Novo E-mail:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($current_email); ?>" required>
            </div>

            <hr style="margin: 20px 0;">
            <p style="text-align: center; font-size: 0.9em; color: #555;">
                Deixe os campos de senha em branco se não quiser alterá-la.
            </p>

            <div class="form-group">
                <label for="current_password">Senha Atual (para confirmar alterações ou mudar senha):</label>
                <input type="password" id="current_password" name="current_password">
            </div>
            <div class="form-group">
                <label for="new_password">Nova Senha (mínimo 8 caracteres):</label>
                <input type="password" id="new_password" name="new_password">
            </div>
            <div class="form-group">
                <label for="confirm_new_password">Confirmar Nova Senha:</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password">
            </div>

            <button type="submit">Salvar Alterações</button>
            
        </form>
        <p style="margin-top: 15px; text-align:center;"><a href="../mensagem/index.php">Voltar para Mensagens</a></p>
    </div>
</body>
</html>
<?php $conn->close(); // Fecha a conexão no final da página ?>