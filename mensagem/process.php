<?php
// SEMPRE inicie a sessão em arquivos que precisam dela ou interagem com auth
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/auth_check.php'; // Garante que o usuário está logado para qualquer ação
include 'db.php'; // Inclui a conexão com o banco

// Criar mensagem
if (isset($_POST['criar'])) {
    // Verifica se o usuário está logado (redundante se auth_check.php está no topo, mas seguro)
    if (!isset($_SESSION['user_id'])) {
         // Poderia redirecionar para login ou mostrar erro
        die("Erro: Usuário não autenticado para criar mensagem.");
    }

    $titulo = trim($_POST['titulo']);
    $conteudo = trim($_POST['conteudo']);
    $user_id = $_SESSION['user_id']; // Pega o ID do usuário logado da SESSÃO

    // Validação simples
    if (empty($titulo) || empty($conteudo)) {
        $_SESSION['message_error'] = "Título e conteúdo são obrigatórios.";
        header("Location: create.php"); // Volta para o formulário de criação
        exit;
    }

    // Insere incluindo o user_id
    $stmt = $conn->prepare("INSERT INTO mensagens (titulo, conteudo, user_id) VALUES (?, ?, ?)");
    // "ssi" -> string, string, integer
    $stmt->bind_param("ssi", $titulo, $conteudo, $user_id);

    if ($stmt->execute()) {
        $_SESSION['message_success'] = "Mensagem criada com sucesso!";
    } else {
        // Em produção, logar o erro $stmt->error
        $_SESSION['message_error'] = "Erro ao criar a mensagem.";
    }
    $stmt->close();
    header("Location: index.php");
    exit;
}

// Editar mensagem
if (isset($_POST['editar'])) {
    // Verifica se o usuário está logado
    if (!isset($_SESSION['user_id'])) {
        die("Erro: Usuário não autenticado para editar mensagem.");
    }

    $id = $_POST['id'];
    $titulo = trim($_POST['titulo']);
    $conteudo = trim($_POST['conteudo']);
    $logged_user_id = $_SESSION['user_id'];

    // Validação simples
    if (empty($titulo) || empty($conteudo) || empty($id)) {
        $_SESSION['message_error'] = "Dados inválidos para edição.";
        // Redirecionar para um local apropriado, talvez a index ou a edit com erro
        header("Location: index.php");
        exit;
    }

    // --- VERIFICAÇÃO DE AUTORIZAÇÃO ---
    // Antes de editar, verifica se o usuário logado é o dono da mensagem
    $stmt_check = $conn->prepare("SELECT user_id FROM mensagens WHERE id = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 1) {
        $mensagem_owner = $result_check->fetch_assoc();
        if ($mensagem_owner['user_id'] == $logged_user_id) {
            // Usuário é o dono, pode editar!
            $stmt_check->close(); // Fecha o statement de verificação

            $stmt_update = $conn->prepare("UPDATE mensagens SET titulo = ?, conteudo = ? WHERE id = ?");
            $stmt_update->bind_param("ssi", $titulo, $conteudo, $id);

            if ($stmt_update->execute()) {
                $_SESSION['message_success'] = "Mensagem atualizada com sucesso!";
            } else {
                // Em produção, logar o erro $stmt_update->error
                $_SESSION['message_error'] = "Erro ao atualizar a mensagem.";
            }
            $stmt_update->close();

        } else {
            // Usuário não é o dono! Acesso negado.
            $stmt_check->close();
            $_SESSION['message_error'] = "Você não tem permissão para editar esta mensagem.";
        }
    } else {
        // Mensagem não encontrada
         $stmt_check->close();
        $_SESSION['message_error'] = "Mensagem não encontrada para edição.";
    }

    header("Location: index.php");
    exit;
}

// Excluir mensagem
if (isset($_GET['excluir'])) {
     // Verifica se o usuário está logado
    if (!isset($_SESSION['user_id'])) {
        die("Erro: Usuário não autenticado para excluir mensagem.");
    }

    $id = $_GET['excluir'];
    $logged_user_id = $_SESSION['user_id'];

     // --- VERIFICAÇÃO DE AUTORIZAÇÃO ---
    // Antes de excluir, verifica se o usuário logado é o dono da mensagem
    $stmt_check = $conn->prepare("SELECT user_id FROM mensagens WHERE id = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 1) {
        $mensagem_owner = $result_check->fetch_assoc();
        if ($mensagem_owner['user_id'] == $logged_user_id) {
             // Usuário é o dono, pode excluir!
            $stmt_check->close();

            $stmt_delete = $conn->prepare("DELETE FROM mensagens WHERE id = ?");
            $stmt_delete->bind_param("i", $id);

            if ($stmt_delete->execute()) {
                 $_SESSION['message_success'] = "Mensagem excluída com sucesso!";
            } else {
                 // Em produção, logar o erro $stmt_delete->error
                $_SESSION['message_error'] = "Erro ao excluir a mensagem.";
            }
             $stmt_delete->close();

        } else {
            // Usuário não é o dono! Acesso negado.
            $stmt_check->close();
            $_SESSION['message_error'] = "Você não tem permissão para excluir esta mensagem.";
        }
    } else {
         // Mensagem não encontrada
         $stmt_check->close();
         $_SESSION['message_error'] = "Mensagem não encontrada para exclusão.";
    }

    header("Location: index.php");
    exit;
}

// Se nenhuma ação foi reconhecida (pouco provável chegar aqui com GET/POST definidos)
// Mas é uma boa prática redirecionar para evitar acesso direto ao process.php
// header("Location: index.php");
// exit;

?>