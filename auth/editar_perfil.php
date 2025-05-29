<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../includes/auth_check.php'; // Garante que apenas usuários logados acessem
require_once '../mensagem/db.php'; // Conexão com o banco

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
// Não feche $conn aqui se o processamento estiver no mesmo arquivo ou se for usado mais abaixo.
// Para esta estrutura, fecharemos no final do script.
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <!-- Se você tem um main_style.css, link ele aqui -->
    <!-- <link rel="stylesheet" href="../css/main_style.css"> -->
    <style>
        /* Estilos do seu formulário (mantidos) */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px; /* Adicionado padding geral para o body */
        }

        .auth-container {
            max-width: 500px;
            margin: 40px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.2s ease-in-out;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #00a5c7;
            outline: none;
        }

        button[type="submit"], .button { /* Adicionada classe .button para consistência */
            background-color: #00a5c7;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%; /* Mantém largura total para botões de formulário */
            font-size: 16px;
            text-align: center;
            text-decoration: none; /* Para a classe .button em links */
            display: inline-block; /* Para a classe .button em links */
            transition: background-color 0.3s ease;
        }
         button[type="submit"]:hover, .button:hover {
            background-color: #0086a8;
        }

        .button-danger { /* Estilo para o botão de excluir */
            background-color: #dc3545;
        }
        .button-danger:hover {
            background-color: #c82333;
        }


        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .text-center { /* Classe utilitária para centralizar texto */
            text-align: center;
        }

        /* ================================= */
        /* CSS para o Modal de Confirmação   */
        /* (Idealmente, mover para main_style.css) */
        /* ================================= */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 25px 30px;
            border: 1px solid #ccc; /* Suavizada a borda */
            width: 90%;
            max-width: 450px; /* Ajustado para ser similar ao auth-container */
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            text-align: center;
        }

        .modal-content h3 {
            margin-top: 0;
            color: #dc3545; /* Vermelho para alerta */
            font-size: 1.5em; /* Tamanho do título do modal */
        }
        .modal-content p {
            margin-bottom: 25px; /* Mais espaço antes dos botões */
            font-size: 1.05em; /* Tamanho do texto do modal */
            color: #555;
        }

        .modal-buttons button {
            padding: 10px 20px;
            margin: 0 10px;
            border-radius: 6px; /* Consistente com outros botões */
            cursor: pointer;
            font-weight: bold;
            font-size: 1em;
            border: none;
            width: auto; /* Botões do modal não precisam ser full-width */
        }

        .modal-buttons .confirm-delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .modal-buttons .confirm-delete-btn:hover {
            background-color: #c82333;
        }

        .modal-buttons .cancel-btn {
            background-color: #6c757d; /* Cinza para cancelar */
            color: white;
        }
         .modal-buttons .cancel-btn:hover {
            background-color: #5a6268;
        }
    </style>
    <link rel="stylesheet" href="ExperienciasCriativamensagem/style.css"> <!-- Seu CSS principal -->
</head>
<body>
    <div class="auth-container">
        <h2>Editar Perfil</h2>

        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="message success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="message error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <form action="processor_editar_perfil.php" method="post"> <!-- Corrigido o nome do arquivo de processamento -->
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

        <!-- ****** SEPARADOR E BOTÃO PARA EXCLUIR CONTA ****** -->
        <hr style="margin: 30px 0; border-color: #ddd;">
        <div class="text-center">
            <button type="button" id="openDeleteModalBtn" class="button button-danger" style="width: auto; padding: 10px 20px; font-size: 0.95em;">Excluir Minha Conta</button>
        </div>
        <!-- *********************************************** -->

        <p style="margin-top: 25px; text-align:center;"><a href="../mensagem/index.php">Voltar para Mensagens</a></p>
    </div>


    <!-- ****** MODAL DE CONFIRMAÇÃO DE EXCLUSÃO ****** -->
    <div id="deleteAccountModal" class="modal">
        <div class="modal-content">
            <h3>Confirmar Exclusão de Conta</h3>
            <p>Tem certeza de que deseja excluir sua conta permanentemente? Esta ação não pode ser desfeita e todos os seus dados associados (como mensagens) serão perdidos.</p>
            <div class="modal-buttons">
                <button type="button" id="cancelDeleteBtn" class="cancel-btn">Cancelar</button>
                <!-- O formulário abaixo será submetido para excluir a conta -->
                <form action="processor_editar_perfil.php" method="POST" style="display: inline;">
                    <button type="submit" name="confirm_delete" class="confirm-delete-btn">Sim, Excluir Conta</button>
                </form>
            </div>
        </div>
    </div>
    <!-- ******************************************* -->

<script>
    // JavaScript para controlar o modal
    var modal = document.getElementById("deleteAccountModal");
    var openBtn = document.getElementById("openDeleteModalBtn");
    var cancelBtn = document.getElementById("cancelDeleteBtn");

    // Abrir o modal
    if (openBtn) { // Verifica se o botão existe antes de adicionar o listener
        openBtn.onclick = function() {
            modal.style.display = "block";
        }
    }

    // Fechar o modal ao clicar em "Cancelar"
    if (cancelBtn) { // Verifica se o botão existe
        cancelBtn.onclick = function() {
            modal.style.display = "none";
        }
    }

    // Fechar o modal se o usuário clicar fora do conteúdo do modal
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

</body>
</html>
<?php
 // A conexão $conn deve ser fechada aqui ou após o último uso dela na página.
 if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
 }
?>