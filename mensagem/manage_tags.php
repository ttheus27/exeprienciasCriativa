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

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Tags</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Gerenciar Tags</h2>

    <?php if (!empty($success)): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="form-box">
        <h3><?= $editTag ? "Editar Tag" : "Nova Tag" ?></h3>
        <form method="POST">
            <?php if ($editTag): ?>
                <input type="hidden" name="tag_id" value="<?= $editTag['id'] ?>">
            <?php endif; ?>
            <label for="nome">Nome da Tag:</label>
            <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($editTag['nome'] ?? '') ?>" required>
            <button type="submit"><?= $editTag ? "Atualizar" : "Criar" ?></button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tags as $tag): ?>
                <tr>
                    <td><?= $tag['id'] ?></td>
                    <td><?= htmlspecialchars($tag['nome']) ?></td>
                    <td class="actions">
                        <a href="?edit=<?= $tag['id'] ?>">Editar</a>
                        <a href="?delete=<?= $tag['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir esta tag?')">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="index.php">← Voltar para o mural</a></p>
</body>
</html>
