<?php
session_start();
require_once '../includes/auth_check.php';
include 'db.php';

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM notificacoes WHERE usuario_id = ? ORDER BY criado_em DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Notificações</title>
</head>
<body>
    <h1>Minhas Notificações</h1>
    <ul>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <li><?= htmlspecialchars($row['mensagem']) ?> - <?= $row['criado_em'] ?></li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
