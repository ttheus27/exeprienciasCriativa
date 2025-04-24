<?php
// Inicia a sessão ANTES de qualquer output ou require que use sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/auth_check.php'; // Verifica se está logado
include 'db.php'; // Conexão com o banco

// Busca todas as mensagens, mas agora incluindo o user_id e o username
// Usamos LEFT JOIN para pegar o nome do usuário que criou a mensagem
// Se user_id for NULL (mensagens antigas ou usuário deletado com SET NULL), username será NULL
$sql = "SELECT m.*, u.username
        FROM mensagens m
        LEFT JOIN usuarios u ON m.user_id = u.id
        ORDER BY m.criado_em DESC";
$result = $conn->query($sql);

// Verifica se a consulta foi bem-sucedida
if (!$result) {
    // Em produção, logar o erro $conn->error
    die("Erro ao buscar mensagens: " . $conn->error); // Para desenvolvimento
}

// Pega o ID do usuário logado para comparações
$logged_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mensagens</title>
    <link rel="stylesheet" href="style.css"> <!-- Link para seu CSS -->
    <!-- Adicionar link para o CSS geral se quiser usar o mesmo estilo -->
    <!-- <link rel="stylesheet" href="../css/style.css"> -->
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
    <!-- Mensagem de Boas vindas e Logout (opcional, mas bom) -->
    <div style="padding: 10px; background-color: #eee; margin-bottom: 20px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center;">
        <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
        <a href="../auth/logout.php" style="text-decoration: none; background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 3px;">Sair</a>
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

    <a href="create.php" class="button-new">+ Nova Mensagem</a> <!-- Adicione uma classe se quiser estilizar -->
    <hr>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="message-box"> <!-- Use a classe que você definiu em style.css -->
                <h3><?= htmlspecialchars($row['titulo']) ?></h3>
                <p><?= nl2br(htmlspecialchars($row['conteudo'])) ?></p>
                <small>
                    Criado em: <?= date('d/m/Y H:i:s', strtotime($row['criado_em'])) ?>
                    <?php if (!empty($row['username'])): // Mostra o nome do usuário se existir ?>
                        por: <?= htmlspecialchars($row['username']) ?>
                    <?php elseif ($row['user_id']): // Se tiver user_id mas não username (usuário deletado talvez)?>
                        por: (usuário desconhecido)
                    <?php else: // Mensagem antiga sem user_id ?>
                         por: (autor não registrado)
                    <?php endif; ?>
                </small><br>

                <?php
                // --- CONTROLE DE ACESSO PARA EDITAR/EXCLUIR ---
                // Verifica se o usuário está logado E se o ID do usuário da mensagem é o mesmo do logado
                if ($logged_user_id !== null && $row['user_id'] == $logged_user_id):
                ?>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="button-edit">Editar</a>
                    <!-- O onclick chama a função JavaScript para confirmar -->
                    <a href="#" onclick="confirmarExclusao(<?= $row['id'] ?>)" class="button-delete" style="color:red;">Excluir</a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Nenhuma mensagem encontrada.</p>
    <?php endif; ?>

    <?php
    // É importante fechar a conexão e o resultado quando terminar
    $result->close();
    $conn->close();
    ?>
</body>
</html>