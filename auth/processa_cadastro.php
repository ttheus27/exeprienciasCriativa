
<?php
session_start(); // Necessário para usar sessões (mensagens e futuro login)
require_once '../mensagem/db.php'; // Inclui a conexão com o banco

// Verifica se o método é POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // --- Validações ---
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "Todos os campos são obrigatórios.";
        header("Location: cadastro.php");
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "As senhas não coincidem.";
        header("Location: cadastro.php");
        exit;
    }

    // Adicionar mais validações se necessário (tamanho da senha, caracteres, etc.)

    // --- Verifica se o usuário já existe ---
    $sql_check = "SELECT id FROM usuarios WHERE username = ?";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $_SESSION['error_message'] = "Este nome de usuário já está em uso.";
            header("Location: cadastro.php");
            exit;
        }
        $stmt_check->close();
    } else {
        // Em produção, logue o erro
        $_SESSION['error_message'] = "Erro ao preparar a verificação de usuário.";
        header("Location: cadastro.php");
        exit;
    }

    // --- Criptografa a Senha (MUITO IMPORTANTE!) ---
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // --- Insere o novo usuário no banco ---
    $sql_insert = "INSERT INTO usuarios (username, password) VALUES (?, ?)"; // Adicione 'role' se necessário

    if ($stmt_insert = $conn->prepare($sql_insert)) {
        // "ss" significa que estamos passando duas strings (username, hashed_password)
        $stmt_insert->bind_param("ss", $username, $hashed_password);

        if ($stmt_insert->execute()) {
            // Cadastro bem-sucedido! Define mensagem de sucesso e redireciona para login
            $_SESSION['success_message'] = "Cadastro realizado com sucesso! Faça o login.";
            header("Location: login.php");
            exit;
        } else {
            // Em produção, logue o erro
            $_SESSION['error_message'] = "Erro ao cadastrar usuário. Tente novamente.";
            header("Location: cadastro.php");
            exit;
        }
        $stmt_insert->close();
    } else {
         // Em produção, logue o erro
        $_SESSION['error_message'] = "Erro ao preparar a inserção. Tente novamente.";
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