<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/auth_check.php';
include 'db.php';

// Check admin permission
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Acesso negado. Apenas administradores podem analisar mensagens.");
}

// Get message ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID da mensagem inválido.");
}

$message_id = (int)$_GET['id'];

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'approved';
        $delivery = 'sent';
    } elseif ($action === 'reject') {
        $status = 'rejected';
        $delivery = 'not_sent';
    } else {
        die("Ação inválida.");
    }

    $stmt = $conn->prepare("UPDATE mensagens SET admin_status = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $delivery, $message_id);

function formatPhoneNumber($rawPhone) {
    $digits = preg_replace('/\D+/', '', $rawPhone); // remove non-digits

    // Remove the '9' after DDD if present and number is 11 digits
    if (strlen($digits) === 11 && substr($digits, 2, 1) === '9') {
        $digits = substr($digits, 0, 2) . substr($digits, 3);
    }

    return '55' . $digits;
}

$successMessage = "";
$responseLogs = [];
// Fetch message data BEFORE running the update
$stmt_msg = $conn->prepare("SELECT m.*, u.username, t.nome AS tag_nome 
                            FROM mensagens m
                            LEFT JOIN usuarios u ON m.user_id = u.id
                            LEFT JOIN tags t ON m.tag_id = t.id
                            WHERE m.id = ?");
$stmt_msg->bind_param("i", $message_id);
$stmt_msg->execute();
$result = $stmt_msg->get_result();
$message = $result->fetch_assoc();

// Build message content safely
$titulo = trim($message['titulo'] ?? '');
$conteudo = trim($message['conteudo'] ?? '');
$messageContent = $titulo . "\n\n" . $conteudo;
if (empty($titulo) && empty($conteudo)) {
    $messageContent = "Uma nova mensagem foi publicada no mural.";
}


if ($stmt->execute()) {
    $successMessage = ($status === 'approved') ? "Mensagem aprovada com sucesso!" : "Mensagem rejeitada com sucesso!";

    // 🔄 Get tag_id from the message
    $tag_id_stmt = $conn->prepare("SELECT tag_id FROM mensagens WHERE id = ?");
    $tag_id_stmt->bind_param("i", $message_id);
    $tag_id_stmt->execute();
    $tag_result = $tag_id_stmt->get_result();
    $tag_data = $tag_result->fetch_assoc();
    $tag_id = $tag_data['tag_id'] ?? null;

    $phoneNumbers = [];

    if ($tag_id) {
        // 🎯 Get interested user IDs
        $user_ids_stmt = $conn->prepare("SELECT usuario_id FROM usuario_interesses WHERE tag_id = ?");
        $user_ids_stmt->bind_param("i", $tag_id);
        $user_ids_stmt->execute();
        $user_ids_result = $user_ids_stmt->get_result();

        $user_ids = [];
        while ($row = $user_ids_result->fetch_assoc()) {
            $user_ids[] = $row['usuario_id'];
        }

        if (!empty($user_ids)) {
            $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
            $types = str_repeat('i', count($user_ids));

            $stmt_phones = $conn->prepare("SELECT telefone FROM usuarios WHERE id IN ($placeholders)");
            $stmt_phones->bind_param($types, ...$user_ids);
            $stmt_phones->execute();
            $phones_result = $stmt_phones->get_result();

            while ($row = $phones_result->fetch_assoc()) {
                if (!empty($row['telefone'])) {
                    $phoneNumbers[] = formatPhoneNumber($row['telefone']);
                }
            }
        }
    }

    // 📤 Send WhatsApp text messages
    foreach ($phoneNumbers as $number) {
        $payload = [
            "to" => $number,
            "body" => $messageContent
        ];

        $ch = curl_init("https://gate.whapi.cloud/messages/text");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer o3P3oYLDnnD3MCsfMQLLIsfTPcfvhApO"
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $responseLogs[] = "Erro ao enviar para $number: " . curl_error($ch);
        } else {
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($http_code >= 300) {
                $responseLogs[] = "Erro HTTP $http_code ao enviar para $number: $response";
            } else {
                $responseLogs[] = "Mensagem enviada para $number: $response";
            }
        }

        curl_close($ch);
    }
} else {
    die("Erro ao atualizar status: " . $stmt->error);
}


}

// Fetch the message
$stmt = $conn->prepare("SELECT m.*, u.username, t.nome AS tag_nome 
                        FROM mensagens m
                        LEFT JOIN usuarios u ON m.user_id = u.id
                        LEFT JOIN tags t ON m.tag_id = t.id
                        WHERE m.id = ?");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$result = $stmt->get_result();
$message = $result->fetch_assoc();

if (!$message) {
    die("Mensagem não encontrada.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Analisar Mensagem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Mensagem para Análise</h2>

    <div class="card">
        
        <h3><?= htmlspecialchars($message['titulo']) ?></h3>
        <?php if (!empty($row['image_path'])): ?>
            <img src="<?= htmlspecialchars($row['image_path']) ?>"
                style="max-height: 200px; height: auto; margin-top: 10px;">
        <?php endif; ?>
        <p><?= nl2br(htmlspecialchars($message['conteudo'])) ?></p>
        <p><strong>Usuário:</strong> <?= htmlspecialchars($message['username']) ?></p>
        <p><strong>Tag:</strong> <?= htmlspecialchars($message['tag_nome']) ?></p>
        <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($message['criado_em'])) ?></p>

        <?php if (!empty($message['image_path'])): ?>
            <img src="<?= htmlspecialchars($message['image_path']) ?>" alt="Imagem" style="max-width: 300px;">
        <?php endif; ?>
    </div>

    <form method="post" style="margin-top: 20px;">
        <button type="submit" name="action" value="approve" style="background-color: green; color: white; padding: 10px 20px;">Aprovar</button>
        <button type="submit" name="action" value="reject" style="background-color: red; color: white; padding: 10px 20px;">Rejeitar</button>
    </form>

    <p><a href="index.php">← Cancelar e voltar</a></p>

    <!-- Popup Modal -->
    <div id="popupModal" style="display:none; position: fixed; top: 20%; left: 50%; transform: translate(-50%, -20%);
        background-color: white; border: 1px solid #ccc; padding: 20px; z-index: 1000; box-shadow: 0px 0px 10px rgba(0,0,0,0.3); text-align: left; width: 90%; max-width: 600px;">
        <h3>Status</h3>
        <p id="popupMessage"></p>
        <div id="responseOutput" style="margin-top: 10px; max-height: 300px; overflow-y: auto;"></div>
        <a href="index.php" style="display: inline-block; margin-top: 10px; padding: 10px 15px; background: #007BFF; color: white; text-decoration: none;">← Voltar para o mural</a>
    </div>

    <script>
    <?php if (!empty($successMessage)): ?>
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("popupMessage").innerText = <?= json_encode($successMessage) ?>;
            const responses = <?= json_encode($responseLogs) ?>;
            const output = document.getElementById("responseOutput");
            output.innerHTML = "<ul>" + responses.map(msg => `<li>${msg}</li>`).join('') + "</ul>";
            document.getElementById("popupModal").style.display = "block";
        });
    <?php endif; ?>
    </script>

</body>
</html>
