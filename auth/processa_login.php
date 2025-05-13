<?php
session_start(); // Essencial para gerenciar o estado de login
require_once '../mensagem/db.php'; // Conexão com o banco

// Verifica se o método é POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login_identifier = trim($_POST['username']); // Campo do formulário pode se chamar 'username' ou 'login_identifier'
    $password = trim($_POST['password']);

    // Validação básica
    if (empty($login_identifier) || empty($password)) {
        $_SESSION['error_message'] = "Usuário/E-mail e senha são obrigatórios.";
        header("Location: login.php");
        exit;
    }

    // --- Busca o usuário no banco PELO USERNAME OU PELO EMAIL ---
    // A query agora verifica se o input corresponde à coluna 'username' OU à coluna 'email'
    $sql = "SELECT id, username, email, password, role FROM usuarios WHERE username = ? OR email = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Passamos o mesmo $login_identifier para ambas as interrogações na query
        // O MySQL/MariaDB fará a verificação OR
        $stmt->bind_param("ss", $login_identifier, $login_identifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // --- Verifica a senha ---
            if (password_verify($password, $user['password'])) {
                // Senha correta! Login bem-sucedido.

                session_regenerate_id(true);

                // Armazena dados do usuário na sessão
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username']; // Sempre armazena o username canônico
                $_SESSION['email'] = $user['email'];     // Pode ser útil ter o email na sessão também
                $_SESSION['role'] = $user['role'];

                // --- Redireciona para a página principal da aplicação ---
                header("Location: ../mensagem/index.php");
                exit;

            } else {
                // Senha incorreta
                $_SESSION['error_message'] = "Usuário/E-mail ou senha inválidos.";
                header("Location: login.php");
                exit;
            }
        } else {
            // Usuário não encontrado (nem por username, nem por email)
            $_SESSION['error_message'] = "Usuário/E-mail ou senha inválidos.";
            header("Location: login.php");
            exit;
        }
        $stmt->close();
    } else {
        // Erro na preparação da query
        $_SESSION['error_message'] = "Erro no sistema de login. Tente novamente.";
        // Em produção, logar $conn->error
        header("Location: login.php");
        exit;
    }
    $conn->close();

} else {
    // Se não for POST, redireciona para o login
    header("Location: login.php");
    exit;
}
?>