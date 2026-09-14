<?php
session_start();
require_once "../config/conexao.php";

if (isset($_SESSION['utilizador_id'])) {
    header("Location: ../dashboard/index.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if ($email === "" || $senha === "") {
        $erro = "Preencha o email e a palavra-passe.";
    } else {
        $sql = "SELECT id, nome, email, senha, perfil, ativo
                FROM utilizadores
                WHERE email = :email
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([":email" => $email]);
        $utilizador = $stmt->fetch();

        if (
            $utilizador &&
            $utilizador["ativo"] &&
            password_verify($senha, $utilizador["senha"])
        ) {
            session_regenerate_id(true);
            $_SESSION["utilizador_id"] = $utilizador["id"];
            $_SESSION["nome"]          = $utilizador["nome"];
            $_SESSION["perfil"]        = $utilizador["perfil"];

            header("Location: ../dashboard/index.php");
            exit;
        } else {
            $erro = "Email ou palavra-passe incorrectos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIGIP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <img src="../assets/img/logo-impressos.png" alt="IMPRESSOS, E.I. — Comércio e Serviços" class="login-logo">
        <h1>SIGIP</h1>
        <p class="login-subtitle">Sistema Web de Gestão</p>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="senha">Palavra-passe</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <button type="submit" class="btn-primary">Entrar</button>
        </form>
    </div>
</body>
</html>