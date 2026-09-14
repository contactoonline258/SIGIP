<?php
session_start();
require_once "../includes/auth.php";
require_once "../config/conexao.php";

/*
|--------------------------------------------------------------------------
| Apenas Administrador pode editar utilizadores
|--------------------------------------------------------------------------
*/
if ($_SESSION["perfil"] !== "Administrador") {
    header("Location: ../dashboard/index.php");
    exit;
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}

$erro = "";

// Buscar dados do utilizador
$stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome   = trim($_POST["nome"]);
    $email  = trim($_POST["email"]);
    $perfil = $_POST["perfil"];
    $senha  = $_POST["senha"];

    if (empty($nome) || empty($email) || empty($perfil)) {
        $erro = "Preencha os campos obrigatórios.";
    } else {
        // Verificar e-mail duplicado
        $stmt = $pdo->prepare("SELECT id FROM utilizadores WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);

        if ($stmt->fetch()) {
            $erro = "Este e-mail já está em uso por outro utilizador.";
        } else {
            if (!empty($senha)) {
                // Se preencheu a palavra-passe, atualiza a hash
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE utilizadores SET nome = ?, email = ?, perfil = ?, senha = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $perfil, $senhaHash, $id]);
            } else {
                // Atualiza sem alterar a palavra-passe
                $stmt = $pdo->prepare("UPDATE utilizadores SET nome = ?, email = ?, perfil = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $perfil, $id]);
            }

            $_SESSION["sucesso"] = "Utilizador atualizado com sucesso!";
            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Utilizador — SIGIP</title>
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
            <h1>Editar Utilizador #<?= $usuario["id"] ?></h1>

            <?php if ($erro): ?>
                <div style="color: red; margin-bottom: 15px;"><?= $erro ?></div>
            <?php endif; ?>

            <form action="editar.php?id=<?= $id ?>" method="POST" style="max-width: 400px;">
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom: 5px;">Nome Completo:</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($usuario["nome"]) ?>" required style="width: 100%; padding: 8px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom: 5px;">E-mail:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($usuario["email"]) ?>" required style="width: 100%; padding: 8px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom: 5px;">Nova Palavra-passe (deixe em branco para manter a atual):</label>
                    <input type="password" name="senha" style="width: 100%; padding: 8px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom: 5px;">Perfil de Acesso:</label>
                    <select name="perfil" required style="width: 100%; padding: 8px;">
                        <option value="Atendente" <?= $usuario["perfil"] === "Atendente" ? "selected" : "" ?>>Atendente</option>
                        <option value="Administrador" <?= $usuario["perfil"] === "Administrador" ? "selected" : "" ?>>Administrador</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Atualizar</button>
                <a href="index.php" style="margin-left: 10px;">Cancelar</a>
            </form>
        </main>
    </div>
</body>
</html>