<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/auth_check.php';
include 'db.php';

$sql = "SELECT m.*, u.username, t.nome AS tag_nome
        FROM mensagens m
        LEFT JOIN usuarios u ON m.user_id = u.id   
        LEFT JOIN tags t ON m.tag_id = t.id      
        ORDER BY m.criado_em DESC";
$result = $conn->query($sql);

if (!$result) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    die("Erro ao buscar mensagens: " . $conn->error);
}

$logged_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$logged_user_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

?>
<?php
$titulo_pagina = 'Cadastro';
include '../includes/logged-header.php';
?>
<div class="msg-header">
    <div class="header-title">
        <h1>Mensagens</h1>
        <!-- Botão de nova mensagem também precisa das classes -->
        <a href="create.php">
            <button class="btn btn-primary py-2 px-4">+ Nova Mensagem</button>
        </a>
    </div>

    <?php
    if (isset($_SESSION['message_success'])) {
        echo '<div class="message success-message" style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 10px; border-radius: 4px;">' . htmlspecialchars($_SESSION['message_success']) . '</div>';
        unset($_SESSION['message_success']);
    }
    if (isset($_SESSION['message_error'])) {
        echo '<div class="message error-message" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 10px; border-radius: 4px;">' . htmlspecialchars($_SESSION['message_error']) . '</div>';
        unset($_SESSION['message_error']);
    }
    ?>
</div>
<hr>

<?php if ($result->num_rows > 0): ?>
    <div class="container column">
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col my-2">
                    <?php
                    $conteudo = htmlspecialchars($row['conteudo']);
                    ?>
                    <div class="message-card  <?php
                    if (!empty($row['image_path']) && mb_strlen($conteudo) > 250) {
                        echo 'aumenta-card';
                    }
                    ?>">
                        <div class="display-tag">
                            <div>
                                <?php if (!empty($row['tag_nome'])): ?>
                                    <span class="message-tag
                            <?php
                            if ($row['tag_nome'] === 'Atualização') {
                                echo 'tag-importante';
                            } elseif ($row['tag_nome'] === 'Aviso') {
                                echo 'tag-aviso';
                            } elseif ($row['tag_nome'] === 'Informativo') {
                                echo 'tag-informativo';
                            } elseif ($row['tag_nome'] === 'Procura-se') {
                                echo 'tag-procurase';
                            }
                            ?>
                            ">
                                        <?= htmlspecialchars($row['tag_nome']) ?>
                                    </span>

                                <?php elseif ($row['tag_id']): ?>
                                    <span class="message-tag">[Tag Removida]</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php
                                if ($logged_user_id !== null && ($logged_user_role === 'admin')):
                                    ?>
                                    <?php if ($row['admin_status'] === 'Pendente'): ?>
                                        <span>
                                            <a href="analisar_mensagem.php?id=<?= $row['id'] ?>" class="btn-analisar">
                                                <button class="btn btn-warning py-2 px-4">Analisar</button>
                                            </a>
                                        </span>
                                    <?php endif; ?>         <?php endif; ?>
                            </div>
                        </div>
                        <div class="message-info">
                            <div class="card-title">
                                <h3><?= htmlspecialchars($row['titulo']) ?></h3>
                            </div>
                            <div class="card-image">
                                <?php if (!empty($row['image_path'])): ?>
                                    <img src="<?= htmlspecialchars($row['image_path']) ?>"
                                        style="max-height: 200px; height: auto; margin-top: 10px;">
                                <?php endif; ?>
                            </div>
                            <div class="display-text">
                                <p class="message-text <?php
                                if (!empty($row['image_path']) && mb_strlen($conteudo) > 250) {
                                    echo 'limita-tamanho-txt';
                                }
                                ?>">
                                    <?php echo nl2br($conteudo); ?>
                                </p>
                            </div>
                        </div>
                        <div>
                            <div class="card-footer">
                                <div class="footer-member card-meta">
                                    <div>
                                        <?= date('d/m/y', strtotime($row['criado_em'])) ?>
                                    </div>
                                    <div>
                                        <?php
                                        $owner_id = $row['user_id'] ?? null;
                                        ?>
                                        <?php if (!empty($row['username'])): ?>
                                            Por: <?= htmlspecialchars($row['username']) ?>
                                        <?php elseif ($owner_id): ?>
                                            Por: (usuário desconhecido)
                                        <?php else: ?>
                                            Por: (autor não registrado)
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="footer-member class-buttons">

                                    <?php
                                    if ($logged_user_id !== null && ($row['user_id'] == $logged_user_id || $logged_user_role === 'admin')):
                                        ?>
                                        <button type="button" onclick="abrirModalConfirmacao(<?= $row['id'] ?>)"
                                            class="btn btn-danger py-2 px-4">Excluir</button>
                                        <a href="edit.php?id=<?= $row['id'] ?>"><button
                                                class="btn btn-primary py-2 px-4">Editar</button></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                            if ($logged_user_id !== null && ($row['user_id'] == $logged_user_id || $logged_user_role === 'admin')):
                                ?>
                                <div class="status-mensagem">
                                    Mensagem enviada: <?= htmlspecialchars($row['status']) ?>
                                    <br>
                                    Mensagem aprovada: <?= htmlspecialchars($row['admin_status']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
<?php else: ?>
    <p>Nenhuma mensagem encontrada.</p>
<?php endif; ?>

<?php
if ($result) {
    $result->close();
}
$conn->close();
?>

<!-- ****** INÍCIO DO NOVO CONTEÚDO ****** -->

<!-- ****** MODAL DE CONFIRMAÇÃO DE EXCLUSÃO ****** -->
<div id="confirmDeleteModal" class="modal">
    <div class="modal-content">
        <h3>Confirmar Exclusão</h3>
        <p>Tem certeza que deseja excluir esta mensagem? Esta ação não pode ser desfeita.</p>
        <div class="modal-buttons">
            <button id="cancelDeleteBtn" class="cancel-btn">Cancelar</button>
            <button id="confirmDeleteBtn" class="confirm-delete-btn">Sim, Excluir</button>
        </div>
    </div>
</div>
<!-- ******************************************* -->

<!-- ****** NOVO SCRIPT PARA CONTROLAR O MODAL ****** -->
<script>
    // Seleciona os elementos do DOM
    const modal = document.getElementById("confirmDeleteModal");
    const cancelBtn = document.getElementById("cancelDeleteBtn");
    const confirmBtn = document.getElementById("confirmDeleteBtn");

    // Variável para guardar o ID da mensagem a ser excluída
    let idParaExcluir = null;

    // Função para ABRIR o modal e GUARDAR o ID
    function abrirModalConfirmacao(id) {
        idParaExcluir = id; // Guarda o ID que foi passado como argumento
        modal.style.display = "block"; // Exibe o modal
    }

    // Ação do botão "Sim, Excluir"
    confirmBtn.onclick = function () {
        if (idParaExcluir !== null) {
            // Redireciona para o script PHP, passando o ID guardado
            window.location.href = 'process.php?excluir=' + idParaExcluir;
        }
    }

    // Ação do botão "Cancelar" para fechar o modal
    cancelBtn.onclick = function () {
        idParaExcluir = null; // Limpa o ID
        modal.style.display = "none";
    }

    // Fecha o modal se o usuário clicar fora da caixa de conteúdo
    window.onclick = function (event) {
        if (event.target == modal) {
            idParaExcluir = null; // Limpa o ID
            modal.style.display = "none";
        }
    }
</script>
<!-- ********************************************** -->

<?php include '../includes/footer.php'; ?>