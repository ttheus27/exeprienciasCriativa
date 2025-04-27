
<?php
// Este script deve ser incluído no início das páginas protegidas
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Garante que a sessão foi iniciada
}

// Verifica se o usuário NÃO está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Se não estiver logado, redireciona para a página de login
    // O caminho '../auth/login.php' assume que este script está em /includes
    // e a página de login está em /auth
    header("Location: ../auth/login.php");
    exit; // Interrompe a execução do script da página protegida
}

// Opcional: Verificar permissões específicas aqui, se necessário
// if ($_SESSION['role'] !== 'admin') {
//     echo "Você não tem permissão para acessar esta página.";
//     // Ou redirecionar para outra página
//     // header("Location: pagina_sem_permissao.php");
//     exit;
// }
?>