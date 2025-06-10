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

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vincular Perfil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Atualizar Perfil</h2>
    <form method="POST">
        <label>Telefone:</label><br>
        <input type="text" name="telefone" required><br><br>

        <label>Área de Atuação:</label><br>
        <input type="text" name="area_atuacao" required><br><br>

        <label>Interesses:</label><br>
        <?php while ($tag = $tags_result->fetch_assoc()): ?>
            <input type="checkbox" name="interesses[]" value="<?= $tag['id'] ?>"> <?= $tag['nome'] ?><br>
        <?php endwhile; ?>

        <br>
        <button type="submit">Salvar</button>
    

    </form>
    <a href="index.php">
        <button>Voltar</button>
    </a>
</body>
</html>
