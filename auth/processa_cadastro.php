<?php
session_start();
require_once '../mensagem/db.php'; // Inclui a conexão

// Verifica se o método é POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']); // <<< PEGA O EMAIL
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // <<< ARMAZENA DADOS DO FORMULÁRIO NA SESSÃO PARA PRESERVAÇÃO EM CASO DE ERRO >>>
    $_SESSION['form_data'] = [
        'username' => $username,
        'email' => $email
        // Não armazene senhas aqui
    ];

    // --- Validações ---
    $errors = []; // Array para armazenar mensagens de erro

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "Todos os campos são obrigatórios.";
    }

    // <<< VALIDAÇÃO DO FORMATO DO E-MAIL >>>
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "O formato do e-mail é inválido.";
    }
    // ***********************************

    // <<< VALIDAÇÃO DO COMPRIMENTO DA SENHA >>>
    if (!empty($password) && strlen($password) < 8) {
        $errors[] = "A senha deve ter no mínimo 8 caracteres.";
    }
    // **************************************

    if ($password !== $confirm_password) {
        $errors[] = "As senhas não coincidem.";
    }

    // Se houver erros de validação, redireciona de volta
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors); // Junta os erros com <br> para exibição
        header("Location: cadastro.php");
        exit;
    }

    // --- Verifica se o usuário ou e-mail já existem ---
    // Modificado para verificar username OU email
    $sql_check = "SELECT id FROM usuarios WHERE username = ? OR email = ?";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param("ss", $username, $email); // Duas strings: username e email
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Para ser mais preciso sobre qual campo duplicou, você precisaria de duas queries
            // ou uma lógica mais complexa para verificar o resultado contra os inputs.
            // Por simplicidade, uma mensagem genérica:
            $_SESSION['error_message'] = "Este nome de usuário ou e-mail já está em uso.";
            $stmt_check->close();
            header("Location: cadastro.php");
            exit;
        }
        $stmt_check->close();
    } else {
        // Em produção, logue o erro
        $_SESSION['error_message'] = "Erro ao preparar a verificação de usuário/e-mail.";
        // Logar: error_log("Erro ao preparar verificação de usuário: " . $conn->error);
        header("Location: cadastro.php");
        exit;
    }

    //  Criptografa a Senha
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    //  Insere o novo usuário no banco (INCLUINDO O EMAIL)
    $sql_insert = "INSERT INTO usuarios (username, email, password) VALUES (?, ?, ?)"; // Adicionado 'email'
    // Role será o default 'user' definido na tabela

    if ($stmt_insert = $conn->prepare($sql_insert)) {
        $stmt_insert->bind_param("sss", $username, $email, $hashed_password); // Três strings: username, email, password

        if ($stmt_insert->execute()) {
            unset($_SESSION['form_data']); // Limpa os dados do formulário da sessão em caso de sucesso
            // Cadastro bem-sucedido
            $_SESSION['success_message'] = "Cadastro realizado com sucesso! Faça o login.";
            header("Location: login.php");
            exit;
        } else {
            // Em produção, logue o erro
            $_SESSION['error_message'] = "Erro ao cadastrar usuário. Tente novamente.";
            // Logar: error_log("Erro ao executar inserção de usuário: " . $stmt_insert->error);
            header("Location: cadastro.php");
            exit;
        }
        $stmt_insert->close();
    } else {
         // Em produção, logue o erro
        $_SESSION['error_message'] = "Erro ao preparar a inserção. Tente novamente.";
        // Logar: error_log("Erro ao preparar inserção de usuário: " . $conn->error);
        header("Location: cadastro.php");
        exit;
    }

    $conn->close();

} else {
    // Se não for POST, redireciona para o cadastro
    header("Location: cadastro.php");
    exit;
}
?>