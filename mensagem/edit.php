<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/auth_check.php';
include 'db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$logged_user_id = $_SESSION['user_id'] ?? null;
$logged_user_role = $_SESSION['role'] ?? null;

if ($id <= 0) {
    $_SESSION['message_error'] = "ID de mensagem inválido.";
    header("Location: index.php");
    exit;
}

// --- LÓGICA DE ACESSO MODIFICADA ---
// Busca a mensagem PELO ID
// Adapte user_id se necessário
$sql_fetch = "SELECT * FROM mensagens WHERE id = ?";
$stmt_fetch = $conn->prepare($sql_fetch);

if (!$stmt_fetch) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    die("Erro na preparação da consulta (buscar mensagem): (" . $conn->errno . ") " . $conn->error . " SQL: " . $sql_fetch);
}

$stmt_fetch->bind_param("i", $id);

if (!$stmt_fetch->execute()) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    die("Erro na execução da consulta (buscar mensagem): (" . $stmt_fetch->errno . ") " . $stmt_fetch->error);
}

$result_fetch = $stmt_fetch->get_result();

$mensagem = null;


if ($result_fetch && $result_fetch->num_rows === 1) {
    $mensagem = $result_fetch->fetch_assoc();

    $owner_id = $mensagem['user_id'] ?? $mensagem['usuario_id'] ?? null;

    if (!($owner_id == $logged_user_id || $logged_user_role === 'admin')) {
        // Não tem permissão
        $_SESSION['message_error'] = "Você não tem permissão para editar esta mensagem.";
        $stmt_fetch->close(); // Fechar o statement antes de sair
        $conn->close();
        header("Location: index.php");
        exit;
    }

    $stmt_fetch->close();

} else {
    // Mensagem não encontrada OU erro ao obter resultado
    $_SESSION['message_error'] = "Mensagem não encontrada (ID: $id).";

    if (isset($stmt_fetch) && $stmt_fetch instanceof mysqli_stmt) {
        $stmt_fetch->close();
    }
    $conn->close();
    header("Location: index.php");
    exit;
}


// BUSCAR TAGS DO BANCO PARA O DROPDOWN
$tags_result = $conn->query("SELECT id, nome FROM tags ORDER BY nome ASC");
if (!$tags_result) {
    die("Erro ao buscar tags: " . $conn->error);
}

?>

<?php
$titulo_pagina = 'Cadastro';
include '../includes/logged-header.php';
?>

<div class="container">
    <div class="d-flex justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12">
            <div class="form-type">
                <form action="process.php" method="POST">
                    <h2 class="text-center">Editar Mensagem</h2>
                    <?php

                    if (isset($_SESSION['message_error'])) {
                        echo '<div class="message error-message">' . htmlspecialchars($_SESSION['message_error']) . '</div>';
                        unset($_SESSION['message_error']);
                    }
                    ?>

                    <input type="hidden" name="id" value="<?= $mensagem['id'] ?>">
                    <div class="form-group">
                        <label for="titulo">Título:</label>
                        <input type="text" id="titulo" name="titulo"
                            value="<?= htmlspecialchars($mensagem['titulo']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="conteudo">Conteúdo:</label>
                        <textarea id="conteudo" name="conteudo" rows="5"
                            required><?= htmlspecialchars($mensagem['conteudo']) ?></textarea>
                        <p id="contador">0 / 500</p>

                        <script>
                            const textarea = document.getElementById('conteudo');
                            const contador = document.getElementById('contador');

                            textarea.addEventListener('input', () => {
                                contador.textContent = `${textarea.value.length} / 500`;
                            });
                        </script>
                    </div>

                    <!--CAMPO DE TAG NA EDIÇÃO COM DADOS DO BANCO-->
                    <div class="form-group">
                        <select name="tag_id" id="tag_id" class="form-select" required>
                            <option value="" selected>-- Selecione uma Tag --</option>
                            <?php while ($tag = $tags_result->fetch_assoc()): ?>
                                <option value="<?= $tag['id'] ?>" <?php
                                  if (isset($mensagem['tag_id']) && $mensagem['tag_id'] == $tag['id'])
                                      echo ' selected';
                                  ?>>
                                    <?= htmlspecialchars($tag['nome']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <br>
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary  py-2 px-4">Voltar</a>
                        <button type="submit" name="editar" class="btn btn-success py-2 px-4">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>

<?php
if (isset($tags_result))
    $tags_result->close();
$conn->close();
?>