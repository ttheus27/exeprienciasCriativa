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
<?php
$titulo_pagina = 'Criar Mensagem';
include '../includes/logged-header.php';
?>
<div class="container">
    <div class="d-flex justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12">
            <div class="form-type">
                <h2 class="text-center">Nova Mensagem</h2>
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
                        <textarea id="conteudo" name="conteudo" rows="5" maxlength="500"
                            placeholder="Conteúdo da mensagem" required></textarea>
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
                        <select name="tag_id" id="tag_id" class="form-select" required>
                            <option value="" selected>-- Selecione uma Tag --</option>
                            <?php while ($tag = $tags_result->fetch_assoc()): ?>
                                <option value="<?= $tag['id'] ?>">
                                    <?= htmlspecialchars($tag['nome']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <br>
                    <div class="form-group">
                        <div class="input-group custom-file-button">
                            <label class="input-group-text" for="inputGroupFile">Escolher arquivo</label>
                            <input type="file" name="image" accept="image/*" class="form-control" id="inputGroupFile">
                        </div>
                    </div>
                    <br>
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary  py-2 px-4">Voltar</a>
                        <button type="submit" name="criar" class="btn btn-success py-2 px-4">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>