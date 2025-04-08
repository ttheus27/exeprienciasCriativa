<?php
// Conexão com banco de dados
$servername = "localhost";
$username = "root"; // ou seu usuário do MySQL
$password = "";     // ou sua senha
$dbname = "seubanco"; // coloque aqui o nome do seu banco

$conn = new mysqli($servername, $username, $password, $dbname);

// Checa conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Quando o formulário for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"] ?? '';
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';

    // Criptografar senha (opcional)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    session_start();
    $_SESSION['email'] = $email;
    header("Location: profile.php"); // redireciona pro perfil
    exit();

    if ($stmt->execute()) {
        echo "<script>alert('Usuário cadastrado com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>Login</title>
</head>

<body>
    <main id="container">
        <form id="login_form">
            <div id="form_header">
                <h1>Login</h1>
                <i id="mode_icon" class="fa-solid fa-moon"></i>
            </div>

            <div id="social_media">
                <a href="#">
                    <img src="img/facebook.png" alt="Facebook">
                </a>

                <a href="#">
                    <img src="img/github.png" alt="Github">
                </a>

                <a href="#">
                    <img src="img/google.png" alt="Google">
                </a>
            </div>

            <div id="inputs">
                <div class="input-box">
                    <label for="name">
                        Name
                        <div class="input-field">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" id="name" name="name">
                        </div>
                    </label>
                </div>
                <div class="input-box">
                    <label for="email">
                        E-mail
                        <div class="input-field">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="text" id="email" name="email">
                        </div>
                    </label>
                </div>
                <div class="input-box">
                    <label for="password">
                        Password
                        <div class="input-field">
                            <i class="fa-solid fa-key"></i>
                            <input type="password" id="password" name="password">
                        </div>
                    </label>

                    <div id="forgot_password">
                        <a href="#">
                            Forgot your password?
                        </a>
                        <span>|</span>
                        <a href="#">
                            Don't have an account?
                        </a>
                    </div>

                </div>
            </div>

            <button type="submit" id="login_button">
                Login
            </button>

        </form>
    </main>
</body>
<script src="script.js"></script>  <!-- Correção aqui -->
</html>