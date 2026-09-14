<?php
require_once "../includes/auth.php";
require_once "../config/conexao.php";
exigirAdministrador();

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM servicos WHERE id = :id LIMIT 1");
$stmt->execute([":id" => $id]);
$servico = $stmt->fetch();

if (!$servico) {
    exit("Serviço não encontrado.");
}

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
        $stmt = $pdo->prepare(
            "UPDATE servicos
             SET nome = :nome,
                 descricao = :descricao,
                 preco_unitario = :preco
             WHERE id = :id"
        );

        $stmt->execute([
            ":nome"      => $nome,
            ":descricao" => $descricao !== "" ? $descricao : null,
            ":preco"     => $preco,
            ":id"        => $id
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
    <title>Editar Serviço — SIGIP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<main class="form-page">
    <div class="form-card">
        <h1>Editar Serviço</h1>

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
                    value="<?= htmlspecialchars($servico["nome"]) ?>"
                    required
                    maxlength="100"
                >
            </div>

            <div class="form-group">
                <label for="descricao">Descrição</label>
                <input
                    type="text"
                    id="descricao"
                    name="descricao"
                    value="<?= htmlspecialchars($servico["descricao"] ?? "") ?>"
                    maxlength="255"
                >
            </div>

            <div class="form-group">
                <label for="preco">Preço base (MT) *</label>
                <input
                    type="number"
                    id="preco"
                    name="preco"
                    value="<?= $servico["preco_unitario"] ?>"
                    min="0"
                    step="0.01"
                    required
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