<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/auth_check.php';
include 'db.php';
// BUSCAR TAGS DO BANCO
$tags_result = $conn->query("SELECT id, nome FROM tags ORDER BY nome ASC");
if (!$tags_result) {
    die("Erro ao buscar tags: " . $conn->error);
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Criar Mensagem</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="new-message">
        <div class="container">


            <h1>Nova Mensagem</h1>

            <!-- Exibir mensagens de erro -->
            <?php
            if (isset($_SESSION['message_error'])) {
                echo '<div class="message error-message" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px;">' . htmlspecialchars($_SESSION['message_error']) . '</div>';
                unset($_SESSION['message_error']);
            }
            ?>

            <form action="process.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titulo">Título:</label>
                    <input type="text" id="titulo" name="titulo" placeholder="Título" required>
                </div>
                <div class="form-group">
                    <label for="conteudo">Conteúdo:</label>
                    <textarea id="conteudo" name="conteudo" rows="5" maxlength="500" placeholder="Conteúdo da mensagem"
                        required></textarea>
                    <p id="contador">0 / 500</p>

                    <script>
                        const textarea = document.getElementById('conteudo');
                        const contador = document.getElementById('contador');

                        textarea.addEventListener('input', () => {
                            contador.textContent = `${textarea.value.length} / 500`;
                        });
                    </script>
                </div>
                <div class="form-group">
                    <label for="tag_id">Tag:</label>
                    <select name="tag_id" id="tag_id" required>
                        <option value="">-- Selecione uma Tag --</option>
                        <?php while ($tag = $tags_result->fetch_assoc()): ?>
                            <option value="<?= $tag['id'] ?>">
                                <?= htmlspecialchars($tag['nome']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Imagem:</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="submit-button" style="margin-top: 15px">
                    <button type="submit" name="criar">Criar Mensagem</button>
                </div>
            </form>
            <br>
            <a href="index.php">← Voltar para Mensagens</a>
        </div>
    </div>
</body>

</html>