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
<?php
$titulo_pagina = 'Minhas Notificações';
include '../includes/logged-header.php';
?>
<div class="container">
    <div class="d-flex justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12">
            <div class="form-type">
                <h2 class="text-center">Minhas Notificações</h2>
                <br>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Mensagem</th>
                                <th style="width: 200px;">Data de Envio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                                    $dataEnvio = new DateTime($row['criado_em']);
                                    ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($row['mensagem']) ?>
                                        </td>
                                        <td>
                                            <?= $dataEnvio->format('d/m/Y \à\s H:i') ?>
                                        </td>
                                    </tr>
                                    <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="2" class="text-center">Nenhuma mensagem encontrada.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <br>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary py-2 px-4">Voltar</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>