<?php
session_start();
require_once '../includes/auth_check.php';
include 'db.php';

$user_id = $_SESSION['user_id'];

// Busca dados do usuário
$stmt = $conn->prepare("SELECT username, email, telefone, area_atuacao FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result()->fetch_assoc();

// Busca interesses
$interesses_query = $conn->prepare("
    SELECT t.nome FROM usuario_interesses ui
    JOIN tags t ON ui.tag_id = t.id
    WHERE ui.usuario_id = ?
");
$interesses_query->bind_param("i", $user_id);
$interesses_query->execute();
$interesses_result = $interesses_query->get_result();

$interesses = [];
while ($row = $interesses_result->fetch_assoc()) {
    $interesses[] = $row['nome'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Meu Perfil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Informações do Perfil</h2>

    <p><strong>Usuário:</strong> <?= htmlspecialchars($user_result['username']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user_result['email']) ?></p>
    <p><strong>Telefone:</strong> <?= htmlspecialchars($user_result['telefone']) ?></p>
    <p><strong>Área de Atuação:</strong> <?= htmlspecialchars($user_result['area_atuacao']) ?></p>
    <p><strong>Interesses:</strong> <?= htmlspecialchars(implode(", ", $interesses)) ?></p>

    <br>
    <a href="vincular_perfil.php"><button>Editar Perfil</button></a>
    <a href="index.php"><button>Voltar</button></a>
</body>
</html>
