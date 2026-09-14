<?php
session_start();
require_once "../includes/auth.php";
require_once "../config/conexao.php";

if ($_SESSION["perfil"] !== "Administrador") {
    header("Location: ../dashboard/index.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome   = trim($_POST["nome"]);
    $email  = trim($_POST["email"]);
    $senha  = $_POST["senha"];
    $perfil = $_POST["perfil"];

    if (empty($nome) || empty($email) || empty($senha) || empty($perfil)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM utilizadores WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erro = "O e-mail introduzido já está registado.";
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilizadores (nome, email, senha, perfil, ativo) VALUES (?, ?, ?, ?, 1)");

            if ($stmt->execute([$nome, $email, $senhaHash, $perfil])) {
                $_SESSION["sucesso"] = "Utilizador criado com sucesso!";
                header("Location: index.php");
                exit;
            } else {
                $erro = "Erro ao registar o utilizador.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Utilizador — SIGIP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="topbar">
        <img src="../assets/img/logo-impressos.png" alt="IMPRESSOS, E.I." class="logo">
        <div class="user-info">
            <span><?= htmlspecialchars($_SESSION["nome"]) ?></span>
            <span class="perfil">(<?= htmlspecialchars($_SESSION["perfil"]) ?>)</span>
            <a href="../auth/logout.php">Sair</a>
        </div>
    </header>

    <div class="layout">
        <aside class="sidebar">
            <a href="../dashboard/index.php">Dashboard</a>
            <a href="../clientes/index.php">Clientes</a>
            <a href="../pedidos/index.php">Pedidos</a>
            <a href="../servicos/index.php">Serviços</a>
            <a href="../pagamentos/index.php">Pagamentos</a>
            <a href="index.php" class="active">Utilizadores</a>
        </aside>

        <main class="content">
            <h1>Novo Utilizador</h1>

            <?php if ($erro): ?>
                <div style="color: red; margin-bottom: 15px;"><?= $erro ?></div>
            <?php endif; ?>

            <form action="novo.php" method="POST" style="max-width: 400px;">
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom: 5px;">Nome Completo:</label>
                    <input type="text" name="nome" required style="width: 100%; padding: 8px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom: 5px;">E-mail:</label>
                    <input type="email" name="email" required style="width: 100%; padding: 8px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom: 5px;">Palavra-passe:</label>
                    <input type="password" name="senha" required style="width: 100%; padding: 8px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom: 5px;">Perfil de Acesso:</label>
                    <select name="perfil" required style="width: 100%; padding: 8px;">
                        <option value="Atendente">Atendente</option>
                        <option value="Administrador">Administrador</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Guardar Utilizador</button>
                <a href="index.php" style="margin-left: 10px;">Cancelar</a>
            </form>
        </main>
    </div>
</body>
</html>