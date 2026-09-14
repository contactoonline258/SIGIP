<?php
require_once "../includes/auth.php";
require_once "../config/conexao.php";
exigirAdministrador();

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome      = trim($_POST["nome"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $preco     = $_POST["preco"] ?? "";

    if ($nome === "" || $preco === "") {
        $erro = "Nome e preço são obrigatórios.";
    } elseif (!is_numeric($preco) || $preco < 0) {
        $erro = "Introduza um preço válido.";
    } else {
        $sql = "INSERT INTO servicos (nome, descricao, preco_unitario)
                VALUES (:nome, :descricao, :preco)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":nome"      => $nome,
            ":descricao" => $descricao !== "" ? $descricao : null,
            ":preco"     => $preco
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
    <title>Novo Serviço — SIGIP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<main class="form-page">
    <div class="form-card">
        <h1>Novo Serviço</h1>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nome">Nome *</label>
                <input type="text" id="nome" name="nome" required maxlength="100">
            </div>

            <div class="form-group">
                <label for="descricao">Descrição</label>
                <input type="text" id="descricao" name="descricao" maxlength="255">
            </div>

            <div class="form-group">
                <label for="preco">Preço base (MT) *</label>
                <input type="number" id="preco" name="preco" min="0" step="0.01" required>
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Guardar Serviço</button>
            </div>
        </form>
    </div>
</main>
</body>
</html>