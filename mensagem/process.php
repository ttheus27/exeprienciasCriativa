<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/auth_check.php'; 
include 'db.php'; 
// PEGA O ID E A ROLE DO USUÁRIO LOGADO
$logged_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$logged_user_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// Criar mensagem
if (isset($_POST['criar'])) {
    // Verifica se o usuário está logado 
    if (!isset($_SESSION['user_id'])) {
        die("Erro: Usuário não autenticado para criar mensagem.");
    }

    $titulo = trim($_POST['titulo']);
    $conteudo = trim($_POST['conteudo']);
    $tag_id = isset($_POST['tag_id']) ? (int)$_POST['tag_id'] : 0; // PEGA O tag_id 
    $user_id = $_SESSION['user_id']; // Pega o ID do usuário logado da SESSÃO

    // Validação simples
    if (empty($titulo) || empty($conteudo)) {
        $_SESSION['message_error'] = "Título e conteúdo são obrigatórios.";
        header("Location: create.php"); 
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO mensagens (titulo, conteudo, user_id, tag_id) VALUES (?, ?, ?, ?)");
    // "ssii" -> string, string, integer (user_id), integer (tag_id)
    $stmt->bind_param("ssii", $titulo, $conteudo, $user_id, $tag_id);

    if ($stmt->execute()) {
        $_SESSION['message_success'] = "Mensagem criada com sucesso!";
    } else {
        $_SESSION['message_error'] = "Erro ao criar a mensagem.";
    }
    $stmt->close();
    header("Location: index.php");
    exit;
}

// Editar mensagem
if (isset($_POST['editar'])) {
    if ($logged_user_id === null) {
        die("Erro: Usuário não autenticado para editar mensagem.");
    }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0; 
    $titulo = trim($_POST['titulo']);
    $conteudo = trim($_POST['conteudo']);
    $tag_id = isset($_POST['tag_id']) ? (int)$_POST['tag_id'] : 0;
    $logged_user_id = $_SESSION['user_id'];

    if (empty($titulo) || empty($conteudo) || empty($id)) {
        $_SESSION['message_error'] = "Dados inválidos para edição.";
        header("Location: index.php");
        exit;
    }

    // VERIFICAÇÃO DE AUTORIZAÇÃO 
    $stmt_check = $conn->prepare("SELECT user_id FROM mensagens WHERE id = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 1) {
        $mensagem_owner = $result_check->fetch_assoc();

        $owner_id = $mensagem_owner['user_id'] ?? null; // Pega o ID do dono
        $stmt_check->close();

        // Verificar permissão: Dono OU Admin
        if ($owner_id == $logged_user_id || $logged_user_role === 'admin') {
            // Permissão concedida! Executa o UPDATE.
            $stmt_update = $conn->prepare("UPDATE mensagens SET titulo = ?, conteudo = ?, tag_id = ? WHERE id = ?");            
            $stmt_update->bind_param("ssii", $titulo, $conteudo, $tag_id, $id);
                
            if ($stmt_update->execute()) {
                $_SESSION['message_success'] = "Mensagem atualizada com sucesso!";
            } else {
                $_SESSION['message_error'] = "Erro ao atualizar a mensagem: " . $stmt_update->error;
            }
            $stmt_update->close();

        } else {
            $_SESSION['message_error'] = "Você não tem permissão para editar esta mensagem.";
        }
    } else {
        $stmt_check->close(); 
        $_SESSION['message_error'] = "Mensagem não encontrada para edição (ID: $id).";
    }

    header("Location: index.php");
    exit;
}

// Excluir mensagem
if (isset($_GET['excluir'])) {
    if ($logged_user_id === null) {
        die("Erro: Usuário não autenticado para excluir mensagem.");
    }

    $id = isset($_GET['excluir']) ? (int)$_GET['excluir'] : 0; 

    if ($id <= 0) {
         $_SESSION['message_error'] = "ID inválido para exclusão.";
         header("Location: index.php");
         exit;
    }

    // VERIFICAÇÃO DE AUTORIZAÇÃO MODIFICADA
    // 1. Buscar o ID do dono da mensagem
    $stmt_check = $conn->prepare("SELECT user_id FROM mensagens WHERE id = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 1) {
        $mensagem_owner = $result_check->fetch_assoc();
        $owner_id = $mensagem_owner['user_id'] ?? null; 
        $stmt_check->close();

        // 2. Verificar permissão: Dono OU Admin
        if ($owner_id == $logged_user_id || $logged_user_role === 'admin') {
            $stmt_delete = $conn->prepare("DELETE FROM mensagens WHERE id = ?");
            $stmt_delete->bind_param("i", $id);

            if ($stmt_delete->execute()) {
                 $_SESSION['message_success'] = "Mensagem excluída com sucesso!";
            } else {
                $_SESSION['message_error'] = "Erro ao excluir a mensagem: " . $stmt_delete->error;
            }
             $stmt_delete->close();

        } else {
            $_SESSION['message_error'] = "Você não tem permissão para excluir esta mensagem.";
        }
    } else {
         // Mensagem com o ID fornecido não foi encontrada
         $stmt_check->close(); 
         $_SESSION['message_error'] = "Mensagem não encontrada para exclusão (ID: $id).";
    }

    header("Location: index.php");
    exit;
}

if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>