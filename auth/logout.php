<?php
session_start(); // Acessa a sessão atual

// Desfaz todas as variáveis da sessão
$_SESSION = array();

// Se for desejado destruir a sessão completamente, remova também o cookie da sessão.
// Nota: Isso destruirá a sessão, e não apenas os dados da sessão!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão
session_destroy();

// Redireciona para a página de login
header("Location: login.php");
exit;
?>