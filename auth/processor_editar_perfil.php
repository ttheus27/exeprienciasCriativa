<?php
session_start();
require_once '../includes/auth_check.php'; // Garante que apenas usuários logados acessem
require_once '../mensagem/db.php'; // Conexão com o banco

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; // ID do usuário logado
    $new_email = trim($_POST['email']);
    $current_password_input = trim($_POST['current_password']); // Senha atual fornecida pelo usuário
    $new_password = trim($_POST['new_password']);
    $confirm_new_password = trim($_POST['confirm_new_password']);

    $errors = [];
    $update_fields = []; // Campos que serão atualizados
    $bind_types = "";    // Tipos para bind_param
    $bind_values = [];   // Valores para bind_param

    // --- Validação do E-mail ---
    if (empty($new_email)) {
        $errors[] = "O campo de e-mail é obrigatório.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "O formato do novo e-mail é inválido.";
    } else {
        // Verificar se o novo e-mail já está em uso por OUTRO usuário
        $stmt_check_email = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        if ($stmt_check_email) {
            $stmt_check_email->bind_param("si", $new_email, $user_id);
            $stmt_check_email->execute();
            if ($stmt_check_email->get_result()->num_rows > 0) {
                $errors[] = "Este novo e-mail já está em uso por outra conta.";
            }
            $stmt_check_email->close();
        } else {
            $errors[] = "Erro ao verificar e-mail. Tente novamente."; // Erro de prepare
        }
    }

    // --- Lógica de Alteração de Senha (se os campos de nova senha foram preenchidos) ---
    $change_password = false;
    if (!empty($new_password) || !empty($confirm_new_password)) {
        $change_password = true;
        if (empty($current_password_input)) {
             $errors[] = "Senha atual é obrigatória para alterar a senha.";
        }
        if (strlen($new_password) < 8) {
            $errors[] = "A nova senha deve ter no mínimo 8 caracteres.";
        }
        if ($new_password !== $confirm_new_password) {
            $errors[] = "As novas senhas não coincidem.";
        }
    }

    // --- Se houver erros de validação até aqui, redireciona ---
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: editar_perfil.php");
        exit;
    }

    // --- Validação da Senha Atual (Necessária se for alterar e-mail ou senha) ---
    // Se o e-mail mudou OU se a senha está sendo alterada, a senha atual é obrigatória
    // Primeiro, busca a senha atual do usuário no banco
    $stmt_current_pass = $conn->prepare("SELECT email, password FROM usuarios WHERE id = ?");
    if (!$stmt_current_pass) {
        $_SESSION['error_message'] = "Erro ao buscar dados do usuário.";
        header("Location: editar_perfil.php"); exit;
    }
    $stmt_current_pass->bind_param("i", $user_id);
    $stmt_current_pass->execute();
    $result_current_pass = $stmt_current_pass->get_result();
    if ($result_current_pass->num_rows !== 1) {
        $_SESSION['error_message'] = "Usuário não encontrado.";
        $stmt_current_pass->close(); header("Location: editar_perfil.php"); exit;
    }
    $user_data = $result_current_pass->fetch_assoc();
    $current_db_password_hash = $user_data['password'];
    $current_db_email = $user_data['email'];
    $stmt_current_pass->close();

    $email_changed = ($new_email !== $current_db_email);

    if ($email_changed || $change_password) {
        if (empty($current_password_input)) {
            $_SESSION['error_message'] = "Senha atual é obrigatória para confirmar as alterações.";
            header("Location: editar_perfil.php");
            exit;
        }
        if (!password_verify($current_password_input, $current_db_password_hash)) {
            $_SESSION['error_message'] = "Senha atual incorreta.";
            header("Location: editar_perfil.php");
            exit;
        }
    }


    // --- Preparar a query de UPDATE ---
    $sql_update_parts = [];

    if ($email_changed) {
        $sql_update_parts[] = "email = ?";
        $bind_types .= "s";
        $bind_values[] = $new_email;
    }

    if ($change_password) { // Se todas as validações de senha passaram
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql_update_parts[] = "password = ?";
        $bind_types .= "s";
        $bind_values[] = $hashed_new_password;
    }

    // Se houver campos para atualizar
    if (!empty($sql_update_parts)) {
        $sql_update_query = "UPDATE usuarios SET " . implode(", ", $sql_update_parts) . " WHERE id = ?";
        $bind_types .= "i"; // Adiciona o tipo para o ID do usuário
        $bind_values[] = $user_id; // Adiciona o ID do usuário ao final dos valores

        $stmt_update = $conn->prepare($sql_update_query);
        if ($stmt_update) {
            // A função call_user_func_array é usada para passar um array de parâmetros para bind_param
            // O primeiro elemento de $bind_values deve ser o $bind_types
            // No PHP 8+ podemos usar o spread operator: $stmt_update->bind_param($bind_types, ...$bind_values);
            // Para compatibilidade mais ampla:
            $params = array_merge([$bind_types], $bind_values);
            $ref_params = [];
            foreach($params as $key => $value) {
                $ref_params[$key] = &$params[$key]; // bind_param espera referências
            }
            call_user_func_array([$stmt_update, 'bind_param'], $ref_params);


            if ($stmt_update->execute()) {
                $_SESSION['success_message'] = "Perfil atualizado com sucesso!";
                // Atualizar dados da sessão se o e-mail mudou (opcional, mas bom)
                if ($email_changed) {
                    $_SESSION['email'] = $new_email;
                }
                 // Se a senha foi alterada, pode ser bom forçar um novo login,
                 // mas para este exemplo, apenas informamos o sucesso.
            } else {
                $_SESSION['error_message'] = "Erro ao atualizar o perfil. Tente novamente.";
                // Logar $stmt_update->error em produção
            }
            $stmt_update->close();
        } else {
            $_SESSION['error_message'] = "Erro ao preparar a atualização. Tente novamente.";
            // Logar $conn->error em produção
        }
    } else {
        // Nenhum campo foi alterado (e-mail é o mesmo, senha não foi tocada)
        $_SESSION['success_message'] = "Nenhuma alteração detectada no perfil.";
    }

    $conn->close();
    header("Location: editar_perfil.php"); // Redireciona de volta para a página de edição (para ver a msg)
    exit;

} else {
    // Se não for POST, redireciona
    header("Location: editar_perfil.php");
    exit;
}
?>