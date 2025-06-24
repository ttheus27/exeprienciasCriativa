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
<?php
$titulo_pagina = 'Editar Perfil';
include '../includes/logged-header.php';
?>


<div class="container">
    <div class="d-flex justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12">
            <div class="form-type">
                <form action="processor_editar_perfil.php" method="post">
                    <h2 class="text-center">Editar Perfil</h2>

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
                    <br>
                    <label for="email">Novo E-mail:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($current_email); ?>"
                        required>

                    <hr style="margin: 20px 0;">
                    <div class="alert alert-warning">
                        Deixe os campos de senha em branco se não quiser alterá-la.
                    </div>

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
                    <br>
                    <div class="d-flex justify-content-between me-3">
                        <a href="index.php" class="btn btn-secondary py-2 px-4">Voltar</a>
                        <button type="submit" class="btn btn-success me-1  py-2 px-4">Salvar Alterações</button>
                    </div>



                </form>

                <!-- ****** SEPARADOR E BOTÃO PARA EXCLUIR CONTA ****** -->
                <hr style="margin: 30px 0; border-color: #ddd;">
                <div class="text-center">
                    <button type="button" id="openDeleteModalBtn" class="btn btn-danger py-2 px-4"
                        style="width: auto; padding: 10px 20px; font-size: 0.95em;">Excluir Minha Conta</button>
                </div>
            </div>


            <!-- ****** MODAL DE CONFIRMAÇÃO DE EXCLUSÃO ****** -->
            <div id="deleteAccountModal" class="modal">
                <div class="modal-content">
                    <h3>Confirmar Exclusão de Conta</h3>
                    <p>Tem certeza de que deseja excluir sua conta permanentemente? Esta ação não pode ser desfeita e
                        todos os seus
                        dados associados (como mensagens) serão perdidos.</p>
                    <div class="modal-buttons">
                        <button type="button" id="cancelDeleteBtn" class="cancel-btn">Cancelar</button>
                        <!-- O formulário abaixo será submetido para excluir a conta -->
                        <form action="processor_editar_perfil.php" method="POST" style="display: inline;">
                            <button type="submit" name="confirm_delete" class="confirm-delete-btn">Sim, Excluir
                                Conta</button>
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
                    openBtn.onclick = function () {
                        modal.style.display = "block";
                    }
                }

                // Fechar o modal ao clicar em "Cancelar"
                if (cancelBtn) { // Verifica se o botão existe
                    cancelBtn.onclick = function () {
                        modal.style.display = "none";
                    }
                }

                // Fechar o modal se o usuário clicar fora do conteúdo do modal
                window.onclick = function (event) {
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }
            </script>
        </div>
    </div>
</div>


<?php include '../includes/footer.php'; ?>
<?php
// A conexão $conn deve ser fechada aqui ou após o último uso dela na página.
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>