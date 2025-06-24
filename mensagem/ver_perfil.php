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
<?php
$titulo_pagina = 'Informações do Perfil';
include '../includes/logged-header.php';
?>
<div class="container">
    <div class="d-flex justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12">
            <div class="form-type">
                <h2 class="text-center">Informações do Perfil</h2>

                <p><strong>Usuário:</strong> <?= htmlspecialchars($user_result['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user_result['email'] ?? 'Não informado') ?></p>
                <p><strong>Telefone:</strong> <?= htmlspecialchars($user_result['telefone'] ?? 'Não informado') ?></p>
                <p><strong>Área de Atuação:</strong>
                    <?= htmlspecialchars($user_result['area_atuacao'] ?? 'Não informado') ?></p>
                <p><strong>Interesses:</strong> <?= htmlspecialchars(implode(", ", $interesses)) ?></p>

                <br>
                <div class="d-flex justify-content-between">
                    <a href="index.php"><button class="btn btn-secondary py-2 px-4">Voltar</button></a>
                    <a href="vincular_perfil.php"><button class="btn btn-primary py-2 px-4">Editar</button></a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>