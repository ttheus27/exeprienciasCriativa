
<?php
// Este script deve ser incluído no início das páginas protegidas
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

// Verifica se o usuário NÃO está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Se não estiver logado, redireciona para a página de login
    header("Location: ../auth/login.php");
    exit; 
}


?>