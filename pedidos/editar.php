<?php
require_once "../includes/auth.php";
require_once "../config/conexao.php";
require_once "../includes/funcoes.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = :id LIMIT 1");
$stmt->execute([":id" => $id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    exit("Pedido não encontrado.");
}

// Bloqueio de edição para pedidos processados/pagos
if ($pedido["estado"] !== "Pendente") {
    exit("Este pedido não pode ser editado porque já foi pago ou processado.");
}

$clientes = $pdo->query("SELECT id, nome, telefone FROM clientes WHERE ativo = TRUE ORDER BY nome")->fetchAll();
$servicos = $pdo->query("SELECT id, nome, preco_unitario FROM servicos WHERE ativo = TRUE ORDER BY nome")->fetchAll();

// Carrega os itens atuais do pedido
$stmt_itens = $pdo->prepare("SELECT servico_id, quantidade FROM itens_pedido WHERE pedido_id = :id");
$stmt_itens->execute([":id" => $id]);
$itens_atuais = $stmt_itens->fetchAll();

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cliente_id  = filter_input(INPUT_POST, "cliente_id", FILTER_VALIDATE_INT);
    $canal       = $_POST["canal_origem"] ?? "";
    $servico_ids = $_POST["servico_id"] ?? [];
    $quantidades = $_POST["quantidade"] ?? [];

    if (!$cliente_id || !in_array($canal, ["Presencial", "WhatsApp", "Email"], true) || empty($servico_ids)) {
        $erro = "Preencha todos os campos obrigatórios e adicione pelo menos um serviço.";
    } else {
        $itens = [];
        $total = 0;

        foreach ($servico_ids as $i => $servico_id) {
            $servico_id = (int) $servico_id;
            $quantidade = (float) ($quantidades[$i] ?? 0);

            if ($servico_id <= 0 || $quantidade <= 0) {
                $erro = "Existem linhas de serviços com dados inválidos.";
                break;
            }

            $stmt_s = $pdo->prepare("SELECT id, nome, preco_unitario FROM servicos WHERE id = :id AND ativo = TRUE LIMIT 1");
            $stmt_s->execute([":id" => $servico_id]);
            $servico = $stmt_s->fetch();

            if (!$servico) {
                $erro = "Serviço seleccionado inválido ou inactivo.";
                break;
            }

            if ($servico["nome"] === "Encadernação") {
                $preco = calcularPrecoEncadernacao($quantidade);
                $subtotal = $preco;
            } else {
                $preco = (float) $servico["preco_unitario"];
                $subtotal = $quantidade * $preco;
            }

            $itens[] = [
                "servico_id" => $servico["id"],
                "quantidade" => $quantidade,
                "preco"      => $preco,
                "subtotal"   => $subtotal
            ];

            $total += $subtotal;
        }

        if ($erro === "") {
            try {
                $pdo->beginTransaction();

                // Atualiza o pedido
                $stmt_up = $pdo->prepare(
                    "UPDATE pedidos 
                     SET cliente_id = :cliente, canal_origem = :canal, valor_total = :total 
                     WHERE id = :id"
                );
                $stmt_up->execute([
                    ":cliente" => $cliente_id,
                    ":canal"   => $canal,
                    ":total"   => $total,
                    ":id"      => $id
                ]);

                // Remove os itens antigos e insere os novos
                $stmt_del = $pdo->prepare("DELETE FROM itens_pedido WHERE pedido_id = :id");
                $stmt_del->execute([":id" => $id]);

                $stmt_in = $pdo->prepare(
                    "INSERT INTO itens_pedido (pedido_id, servico_id, quantidade, preco_aplicado, subtotal)
                     VALUES (:pedido, :servico, :quantidade, :preco, :subtotal)"
                );

                foreach ($itens as $item) {
                    $stmt_in->execute([
                        ":pedido"     => $id,
                        ":servico"    => $item["servico_id"],
                        ":quantidade" => $item["quantidade"],
                        ":preco"      => $item["preco"],
                        ":subtotal"   => $item["subtotal"]
                    ]);
                }

                $pdo->commit();
                header("Location: visualizar.php?id=" . $id);
                exit;

            } catch (PDOException $e) {
                $pdo->rollBack();
                $erro = "Não foi possível atualizar o pedido.";
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
    <title>Editar Pedido #<?= $pedido["id"] ?> — SIGIP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<main class="form-page">
    <div class="form-card pedido-card">
        <h1>Editar Pedido #<?= $pedido["id"] ?></h1>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="cliente_id">Cliente *</label>
                <select name="cliente_id" id="cliente_id" required>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente["id"] ?>" <?= $cliente["id"] == $pedido["cliente_id"] ? "selected" : "" ?>>
                            <?= htmlspecialchars($cliente["nome"]) ?> — <?= htmlspecialchars($cliente["telefone"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="canal_origem">Canal de Origem *</label>
                <select name="canal_origem" id="canal_origem" required>
                    <option value="Presencial" <?= $pedido["canal_origem"] === "Presencial" ? "selected" : "" ?>>Presencial</option>
                    <option value="WhatsApp" <?= $pedido["canal_origem"] === "WhatsApp" ? "selected" : "" ?>>WhatsApp</option>
                    <option value="Email" <?= $pedido["canal_origem"] === "Email" ? "selected" : "" ?>>Email</option>
                </select>
            </div>

            <h2>Serviços</h2>
            <div id="servicos-container">
                <?php foreach ($itens_atuais as $item_atual): ?>
                    <div class="item-pedido">
                        <select name="servico_id[]" class="servico-select" required>
                            <option value="">Seleccione o serviço</option>
                            <?php foreach ($servicos as $servico): ?>
                                <option 
                                    value="<?= $servico["id"] ?>" 
                                    data-preco="<?= $servico["preco_unitario"] ?>"
                                    <?= $servico["id"] == $item_atual["servico_id"] ? "selected" : "" ?>
                                >
                                    <?= htmlspecialchars($servico["nome"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <input type="number" name="quantidade[]" class="quantidade" min="1" step="1" value="<?= $item_atual["quantidade"] ?>" required>
                        <span class="subtotal">0,00 MT</span>
                        <button type="button" class="btn-action danger btn-remover">×</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" id="adicionar-servico" class="btn-secondary">+ Adicionar serviço</button>

            <div class="total-pedido">
                Total: <strong id="total"><?= number_format($pedido["valor_total"], 2, ",", ".") ?> MT</strong>
            </div>

            <div class="form-actions">
                <a href="visualizar.php?id=<?= $pedido["id"] ?>" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Guardar Alterações</button>
            </div>
        </form>
    </div>
</main>
<script src="../assets/js/script.js"></script>
</body>
</html>