<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Inicia a sessão ANTES de qualquer output ou require que use sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/auth_check.php';
include 'db.php'; // Inclui DB para buscar tags e mensagem

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$logged_user_id = $_SESSION['user_id'] ?? null; // Usar ?? null para segurança
$logged_user_role = $_SESSION['role'] ?? null;

if ($id <= 0) {
    $_SESSION['message_error'] = "ID de mensagem inválido.";
    header("Location: index.php");
    exit;
}

// --- LÓGICA DE ACESSO MODIFICADA ---
// Busca a mensagem PELO ID
// !! Adapte user_id se necessário !!
$sql_fetch = "SELECT * FROM mensagens WHERE id = ?";
$stmt_fetch = $conn->prepare($sql_fetch);

// ****** ADICIONAR ESTA VERIFICAÇÃO ******
if (!$stmt_fetch) {
    // Prepare falhou! Mostrar o erro do banco e interromper.
    // Em produção, você deve logar o erro em vez de usar die().
    ini_set('display_errors', 1); error_reporting(E_ALL); // Forçar exibição para debug
    die("Erro na preparação da consulta (buscar mensagem): (" . $conn->errno . ") " . $conn->error . " SQL: " . $sql_fetch);
}
// **************************************

$stmt_fetch->bind_param("i", $id);

// É bom verificar o execute também
if (!$stmt_fetch->execute()) {
     ini_set('display_errors', 1); error_reporting(E_ALL); // Forçar exibição para debug
     die("Erro na execução da consulta (buscar mensagem): (" . $stmt_fetch->errno . ") " . $stmt_fetch->error);
}

$result_fetch = $stmt_fetch->get_result();

$mensagem = null; // Inicializa a variável

// Linha 28 (aproximadamente): Verifica se $result_fetch é válido antes de usar
if ($result_fetch && $result_fetch->num_rows === 1) {
    $mensagem = $result_fetch->fetch_assoc();
    // !! Adapte user_id se necessário !!
    $owner_id = $mensagem['user_id'] ?? $mensagem['usuario_id'] ?? null; // Ajuste conforme seu nome de coluna

    // Permissão já foi verificada aqui na lógica anterior? Vamos refazer:
     if (!($owner_id == $logged_user_id || $logged_user_role === 'admin')) {
        // Não tem permissão
         $_SESSION['message_error'] = "Você não tem permissão para editar esta mensagem.";
         $stmt_fetch->close(); // Fechar o statement antes de sair
         $conn->close();
         header("Location: index.php");
         exit;
    }
    // Se chegou aqui, tem permissão E a mensagem foi encontrada

    // Fechar o statement AGORA que já pegamos os dados
    $stmt_fetch->close();

} else {
    // Mensagem não encontrada OU erro ao obter resultado
    $_SESSION['message_error'] = "Mensagem não encontrada (ID: $id).";
    // Linha 46 (aproximadamente): Tenta fechar stmt_fetch se ele existir
    // Verifica se $stmt_fetch é um objeto válido antes de fechar
    if (isset($stmt_fetch) && $stmt_fetch instanceof mysqli_stmt) {
         $stmt_fetch->close();
    }
    $conn->close();
    header("Location: index.php");
    exit;
}

// Se chegou até aqui, $mensagem existe e o usuário tem permissão.
// Continuar para buscar as tags...

// <<< BUSCAR TAGS DO BANCO PARA O DROPDOWN >>>
$tags_result = $conn->query("SELECT id, nome FROM tags ORDER BY nome ASC");
if (!$tags_result) {
    die("Erro ao buscar tags: " . $conn->error);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editar Mensagem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Editar Mensagem</h1>
    <?php
       // Exibir erro da sessão, se houver (vindo de process.php, por exemplo)
       if (isset($_SESSION['message_error'])) {
            echo '<div class="message error-message">'.htmlspecialchars($_SESSION['message_error']).'</div>';
            unset($_SESSION['message_error']);
       }
    ?>

    <form action="process.php" method="POST">
        <input type="hidden" name="id" value="<?= $mensagem['id'] ?>">
        <div class="form-group">
             <label for="titulo">Título:</label>
            <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($mensagem['titulo']) ?>" required>
        </div>
        <div class="form-group">
             <label for="conteudo">Conteúdo:</label>
            <textarea id="conteudo" name="conteudo" rows="5" required><?= htmlspecialchars($mensagem['conteudo']) ?></textarea>
        </div>

        <!-- ****** CAMPO DE TAG NA EDIÇÃO COM DADOS DO BANCO ****** -->
        <div class="form-group">
            <label for="tag_id">Tag:</label>
            <select name="tag_id" id="tag_id" required>
                <option value="">-- Selecione uma Tag --</option>
                <?php while ($tag = $tags_result->fetch_assoc()): ?>
                    <option value="<?= $tag['id'] ?>"
                        <?php
                        // Pré-seleciona a tag atual da mensagem
                        // !! Adapte tag_id se necessário !!
                        if (isset($mensagem['tag_id']) && $mensagem['tag_id'] == $tag['id']) echo ' selected';
                        ?> >
                        <?= htmlspecialchars($tag['nome']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <!-- *************************************************** -->

        <button type="submit" name="editar">Salvar Alterações</button>
    </form>
    <br>
    <a href="index.php">← Voltar para Mensagens</a>
</body>
</html>
<?php
 if(isset($tags_result)) $tags_result->close();
 $conn->close();
?>