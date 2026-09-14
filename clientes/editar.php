<?php
require_once "../includes/auth.php";
require_once "../config/conexao.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

$sql = "SELECT * FROM clientes WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([":id" => $id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    exit("Cliente não encontrado.");
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome     = trim($_POST["nome"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $email    = trim($_POST["email"] ?? "");

    if ($nome === "" || $telefone === "") {
        $erro = "Nome e telefone são obrigatórios.";
    } else {
        $sql = "UPDATE clientes
                SET nome = :nome,
                    telefone = :telefone,
                    email = :email
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":nome"     => $nome,
            ":telefone" => $telefone,
            ":email"    => $email !== "" ? $email : null,
            ":id"       => $id
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
    <title>Editar Cliente — SIGIP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<main class="form-page">
    <div class="form-card">
        <h1>Editar Cliente</h1>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nome">Nome *</label>
                <input
                    type="text"
                    id="nome"
                    name="nome"
                    value="<?= htmlspecialchars($cliente["nome"]) ?>"
                    required
                    maxlength="120"
                >
            </div>

            <div class="form-group">
                <label for="telefone">Telefone *</label>
                <input
                    type="text"
                    id="telefone"
                    name="telefone"
                    value="<?= htmlspecialchars($cliente["telefone"]) ?>"
                    required
                    maxlength="30"
                >
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($cliente["email"] ?? "") ?>"
                    maxlength="120"
                >
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Guardar Alterações</button>
            </div>
        </form>
    </div>
</main>
</body>
</html>