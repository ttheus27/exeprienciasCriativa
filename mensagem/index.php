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

// Verifica se a consulta foi bem-sucedida
if (!$result) {
    ini_set('display_errors', 1); // Temporário para debug
    error_reporting(E_ALL);     // Temporário para debug
    die("Erro ao buscar mensagens: " . $conn->error);
}

// Pega o ID e a ROLE do usuário logado para comparações
$logged_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$logged_user_role = isset($_SESSION['role']) ? $_SESSION['role'] : null; // <<< Pega a role da sessão

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mensagens</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function confirmarExclusao(id) {
            // Pede confirmação antes de redirecionar para a exclusão
            if (confirm("Tem certeza que deseja excluir esta mensagem?")) {
                window.location.href = 'process.php?excluir=' + id;
            }
        }
    </script>
</head>
<body>
    <!-- Mensagem de Boas vindas e Logout -->
    <div style="padding: 10px; background-color: #eee; margin-bottom: 20px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center;">
        <span>
            Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?>!
            (Role: <?php echo htmlspecialchars($logged_user_role); ?>) 
        </span>
        <a href="../auth/editar_perfil.php" class = "botao botao-editar">Editar conta</a>
        <a href="notificacoes.php">
            <button>Ver Notificações</button>
        </a>
        <a href="vincular_perfil.php">
            <button>Vincular Perfil</button>
        </a>
        <a href="ver_perfil.php">
            <button>Ver Perfil</button>
        </a>

        <a href="../auth/logout.php" class = "botao botao-excluir">Sair</a>
    </div>

    <h1>Mensagens</h1>

    <!-- Exibir mensagens de feedback da sessão (sucesso/erro) -->
    <?php
        if (isset($_SESSION['message_success'])) {
            echo '<div class="message success-message" style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px;">' . htmlspecialchars($_SESSION['message_success']) . '</div>';
            unset($_SESSION['message_success']);
        }
        if (isset($_SESSION['message_error'])) {
            echo '<div class="message error-message" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px;">' . htmlspecialchars($_SESSION['message_error']) . '</div>';
            unset($_SESSION['message_error']);
        }
    ?>

    <a href="create.php" class="button-new">+ Nova Mensagem</a>
    <hr>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="message-box">
            <?php if (!empty($row['tag_nome'])): ?>
                    <span class="message-tag" style="background-color: #e2e3e5; color: #4f545c; padding: 3px 8px; border-radius: 10px; font-size: 0.8em; font-weight: bold; margin-right: 10px;">
                        <?= htmlspecialchars($row['tag_nome']) ?>
                    </span>
                 <?php elseif ($row['tag_id']): ?>
                     <span class="message-tag" style="/* estilo diferente talvez */">[Tag Removida]</span>
                 <?php endif; ?>
                <h3><?= htmlspecialchars($row['titulo']) ?></h3>
                <!-- <h5><?= htmlspecialchars($row['id']) ?></h5> -->
                <?php if (!empty($row['image_path'])): ?>
                    <img src="<?= htmlspecialchars($row['image_path']) ?>" style="max-height: 200px; height: auto; margin-top: 10px;">
                <?php endif; ?>
                <p><?= nl2br(htmlspecialchars($row['conteudo'])) ?></p>
                <small>
                    Criado em: <?= date('d/m/Y H:i:s', strtotime($row['criado_em'])) ?>
                    <?php

                        $owner_id = $row['user_id'] ?? null; 
                    ?>
                    <?php if (!empty($row['username'])): ?>
                        por: <?= htmlspecialchars($row['username']) ?> 
                    <?php elseif ($owner_id): ?>
                        por: (usuário desconhecido) 
                    <?php else: ?>
                         por: (autor não registrado)
                    <?php endif; ?>
                </small><br>

                <?php

                if ($logged_user_id !== null && ($owner_id == $logged_user_id || $logged_user_role === 'admin')):
                ?><br>
                    <a href="edit.php?id=<?= $row['id'] ?>"  class = "botao botao-editar">Editar</a>
                    <a href="#" onclick="confirmarExclusao(<?= $row['id'] ?>)"  class = "botao botao-excluir">Excluir</a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Nenhuma mensagem encontrada.</p>
    <?php endif; ?>

    <?php
    // É importante fechar a conexão e o resultado quando terminar
    if ($result) { $result->close(); } 
    $conn->close();
    ?>
</body>
</html>