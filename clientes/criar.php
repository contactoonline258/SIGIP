<?php
require_once "../includes/auth.php";
require_once "../config/conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome     = trim($_POST["nome"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $email    = trim($_POST["email"] ?? "");

    if ($nome === "" || $telefone === "") {
        $erro = "Nome e telefone são obrigatórios.";
    } else {
        $sql = "INSERT INTO clientes (nome, telefone, email)
                VALUES (:nome, :telefone, :email)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":nome"     => $nome,
            ":telefone" => $telefone,
            ":email"    => $email !== "" ? $email : null
        ]);

        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Cliente — SIGIP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header class="topbar">
    <img src="../assets/img/logo-impressos.png" alt="IMPRESSOS, E.I." class="logo">
</header>

<main class="form-page">
    <div class="form-card">
        <h1>Novo Cliente</h1>
        <p>Registar um novo cliente.</p>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nome">Nome *</label>
                <input type="text" id="nome" name="nome" required maxlength="120">
            </div>

            <div class="form-group">
                <label for="telefone">Telefone *</label>
                <input type="text" id="telefone" name="telefone" required maxlength="30">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="120">
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Guardar Cliente</button>
            </div>
        </form>
    </div>
</main>
</body>
</html>