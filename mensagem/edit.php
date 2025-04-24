<?php
// Inicia a sessão ANTES de qualquer output ou require que use sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/auth_check.php'; // Garante que o usuário está logado
include 'db.php'; // Conexão

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0; // Pega o ID e garante que é inteiro
$logged_user_id = $_SESSION['user_id'];

if ($id <= 0) {
    $_SESSION['message_error'] = "ID de mensagem inválido.";
    header("Location: index.php");
    exit;
}

// Busca a mensagem E verifica se o usuário logado é o dono
$stmt = $conn->prepare("SELECT * FROM mensagens WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $logged_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // Usuário é o dono, busca os dados
    $mensagem = $result->fetch_assoc();
} else {
    // Mensagem não encontrada OU o usuário não é o dono
    $_SESSION['message_error'] = "Mensagem não encontrada ou você não tem permissão para editá-la.";
    $stmt->close();
    $conn->close();
    header("Location: index.php");
    exit; // Interrompe a execução
}
$stmt->close();
// $conn->close(); // Não feche a conexão aqui se precisar dela mais abaixo na página (improvável neste caso)

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editar Mensagem</title>
    <link rel="stylesheet" href="style.css"> <!-- Seu CSS de mensagens -->
    <!-- <link rel="stylesheet" href="../css/style.css"> --> <!-- CSS geral opcional -->
</head>
<body>
    <h1>Editar Mensagem</h1>

     <!-- Exibir mensagens de erro, se houver (ex: se validação falhar no process.php e redirecionar de volta) -->
    <?php
        if (isset($_SESSION['message_error'])) {
            echo '<div class="message error-message" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px;">' . htmlspecialchars($_SESSION['message_error']) . '</div>';
            unset($_SESSION['message_error']);
        }
    ?>

    <form action="process.php" method="POST">
        <!-- Campo oculto com o ID da mensagem -->
        <input type="hidden" name="id" value="<?= $mensagem['id'] ?>">

        <div class="form-group"> <!-- Similar ao formulário de login/cadastro -->
             <label for="titulo">Título:</label>
            <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($mensagem['titulo']) ?>" required>
        </div>

        <div class="form-group">
             <label for="conteudo">Conteúdo:</label>
            <textarea id="conteudo" name="conteudo" rows="5" required><?= htmlspecialchars($mensagem['conteudo']) ?></textarea>
        </div>

        <button type="submit" name="editar">Salvar Alterações</button>
    </form>
    <br>
    <a href="index.php">← Voltar para Mensagens</a>
</body>
</html>

<?php $conn->close(); // Fecha a conexão no final da página ?>