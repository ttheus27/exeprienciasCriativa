
<?php

ini_set('display_errors', 1); // Mostra erros na tela
ini_set('display_startup_errors', 1); // Mostra erros de inicialização
error_reporting(E_ALL); // Reporta todos os tipos de erros

session_start(); // Essencial para gerenciar o estado de login
require_once '../mensagem/db.php'; // Conexão com o banco

// Verifica se o método é POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validação básica
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Usuário e senha são obrigatórios.";
        header("Location: login.php");
        exit;
    }

    // --- Busca o usuário no banco ---
    $sql = "SELECT id, username, password, role FROM usuarios WHERE username = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // --- Verifica a senha ---
            if (password_verify($password, $user['password'])) {
                // Senha correta! Login bem-sucedido.

                // Regenera o ID da sessão por segurança
                session_regenerate_id(true);

                // Armazena dados do usuário na sessão
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // Armazena a permissão/role

                // --- Redireciona para a página principal da aplicação ---
                header("Location: ../mensagem/index.php");
                exit;

            } else {
                // Senha incorreta
                $_SESSION['error_message'] = "Usuário ou senha inválidos.";
                header("Location: login.php");
                exit;
            }
        } else {
            // Usuário não encontrado
            $_SESSION['error_message'] = "Usuário ou senha inválidos.";
            header("Location: login.php");
            exit;
        }
        $stmt->close();
    } else {
        // Erro na preparação da query
        $_SESSION['error_message'] = "Erro no sistema de login. Tente novamente.";
        header("Location: login.php");
        exit;
    }
    $conn->close();

} else {
    // redireciona para o login
    header("Location: login.php");
    exit;
}
?>