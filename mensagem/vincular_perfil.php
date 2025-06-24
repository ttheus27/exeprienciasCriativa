<?php
session_start();
require_once '../includes/auth_check.php';
include 'db.php';

$user_id = $_SESSION['user_id'];

// Salvar se formulário enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefone = trim($_POST['telefone']);
    $area = trim($_POST['area_atuacao']);
    $interesses = $_POST['interesses'] ?? [];

    // Atualiza usuario
    $stmt = $conn->prepare("UPDATE usuarios SET telefone = ?, area_atuacao = ? WHERE id = ?");
    $stmt->bind_param("ssi", $telefone, $area, $user_id);
    $stmt->execute();

    // Limpa interesses antigos
    $conn->query("DELETE FROM usuario_interesses WHERE usuario_id = $user_id");

    // Insere novos interesses
    $stmtTag = $conn->prepare("INSERT INTO usuario_interesses (usuario_id, tag_id) VALUES (?, ?)");
    foreach ($interesses as $tag_id) {
        $stmtTag->bind_param("ii", $user_id, $tag_id);
        $stmtTag->execute();
    }

    $_SESSION['message_success'] = "Perfil atualizado com sucesso!";
    header("Location: index.php");
    exit;
}

// Buscar tags
$tags_result = $conn->query("SELECT * FROM tags");
?>
<?php
$titulo_pagina = 'Atualizar Perfil';
include '../includes/logged-header.php';
?>
<div class="container">
    <div class="d-flex justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12">
            <form method="POST">
                <h2 class="text-center">Atualizar Perfil</h2>
                <br>
                <label>Telefone:</label><br>
                <input type="text" name="telefone" required><br><br>

                <label>Área de Atuação:</label><br>
                <input type="text" name="area_atuacao" required><br><br>

                <label>Interesses:</label><br>
                <?php while ($tag = $tags_result->fetch_assoc()): ?>
                    <input type="checkbox" name="interesses[]" value="<?= $tag['id'] ?>"> <?= $tag['nome'] ?><br>
                <?php endwhile; ?>

                <br>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary py-2 px-4">Voltar</a>
                    <button type="submit" class="btn btn-success py-2 px-4">Salvar</button>
                </div>

            </form>

        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>