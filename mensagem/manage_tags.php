<?php
session_start();
require_once '../includes/auth_check.php'; // Optional auth
include '../mensagem/db.php'; // Adjust to your DB connection path

$errors = [];
$success = "";

// Handle CREATE or UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tagId = $_POST['tag_id'] ?? null;
    $nome = trim($_POST['nome']);

    if (empty($nome)) {
        $errors[] = "O nome da tag não pode estar vazio.";
    } else {
        if ($tagId) {
            // UPDATE
            $stmt = $conn->prepare("UPDATE tags SET nome = ? WHERE id = ?");
            $stmt->bind_param("si", $nome, $tagId);
            if ($stmt->execute()) {
                $success = "Tag atualizada com sucesso.";
            } else {
                $errors[] = "Erro ao atualizar a tag: " . $stmt->error;
            }
        } else {
            // CREATE
            $stmt = $conn->prepare("INSERT INTO tags (nome) VALUES (?)");
            $stmt->bind_param("s", $nome);
            if ($stmt->execute()) {
                $success = "Tag criada com sucesso.";
            } else {
                $errors[] = "Erro ao criar a tag: " . $stmt->error;
            }
        }
    }
}

// Handle DELETE
if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tags WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    if ($stmt->execute()) {
        $success = "Tag excluída com sucesso.";
    } else {
        $errors[] = "Erro ao excluir a tag: " . $stmt->error;
    }
}

// Fetch all tags
$tags = [];
$result = $conn->query("SELECT * FROM tags ORDER BY id ASC");
while ($row = $result->fetch_assoc()) {
    $tags[] = $row;
}

// Pre-fill edit form if editing
$editTag = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tags WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editResult = $stmt->get_result();
    $editTag = $editResult->fetch_assoc();
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
                <h2 class="text-center">Gerenciar Tags</h2>

                <?php if (!empty($success)): ?>
                    <p class="success"><?= htmlspecialchars($success) ?></p>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <p class="error"><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
                <br>
                <div class="form-box">
                    <form method="POST">
                        <h3 class="text-center"><?= $editTag ? "Editar Tag" : "Nova Tag" ?></h3>
                        <?php if ($editTag): ?>
                            <input type="hidden" name="tag_id" value="<?= $editTag['id'] ?>">
                        <?php endif; ?>
                        <label for="nome">Nome da Tag:</label>
                        <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($editTag['nome'] ?? '') ?>"
                            required>
                        <div class="d-flex justify-content-end me-3">
                            <?php
                            if ($editTag):
                                ?>
                                <a href="manage_tags.php" class="btn btn-secondary mt-2 me-2 py-2 px-4">Cancelar</a>
                                <?php
                            endif;
                            ?>

                            <button type="submit" class="btn btn-success mt-2 me-1 py-2 px-4">
                                <?= $editTag ? "Atualizar" : "Criar" ?>
                            </button>
                        </div>
                    </form>
                </div>
                <br>
                <div class="table-responsive">

                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tags)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">Nenhuma tag encontrada.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tags as $tag): ?>
                                    <tr>
                                        <td><?= $tag['id'] ?></td>
                                        <td><?= htmlspecialchars($tag['nome']) ?></td>

                                        <td class="text-end">
                                            <a href="?edit=<?= $tag['id'] ?>" class="btn btn-primary py-2 px-4">Editar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <br>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary py-2 px-4">Voltar</a>
                </div>

            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>