<?php
require_once "../includes/auth.php";
require_once "../config/conexao.php";

function calcularPrecoEncadernacao($folhas) {
    if ($folhas <= 0) {
        return 0;
    }
    return ceil($folhas / 50) * 30;
}

// Carrega clientes e serviços usando 'preco_unitario' e filtro 'ativo = 1'
$clientes = $pdo->query("SELECT id, nome, telefone FROM clientes ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$servicos = $pdo->query("SELECT id, nome, preco_unitario FROM servicos WHERE ativo = 1 ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cliente_id  = filter_input(INPUT_POST, "cliente_id", FILTER_VALIDATE_INT);
    $canal       = $_POST["canal_origem"] ?? "";
    $servico_ids = $_POST["servico_id"] ?? [];
    $quantidades = $_POST["quantidade"] ?? [];

    if (!$cliente_id) {
        $erro = "Seleccione um cliente.";
    } elseif (!in_array($canal, ["Presencial", "WhatsApp", "Email"], true)) {
        $erro = "Seleccione um canal válido.";
    } elseif (empty($servico_ids) || empty($quantidades)) {
        $erro = "Adicione pelo menos um serviço.";
    } else {
        $itens = [];
        $total = 0;

        foreach ($servico_ids as $i => $servico_id) {
            $servico_id = (int) $servico_id;
            $quantidade = (float) ($quantidades[$i] ?? 0);

            if ($servico_id <= 0 || $quantidade <= 0) {
                $erro = "Existem dados inválidos no pedido.";
                break;
            }

            $stmt = $pdo->prepare("SELECT id, nome, preco_unitario FROM servicos WHERE id = :id AND ativo = 1 LIMIT 1");
            $stmt->execute([":id" => $servico_id]);
            $servico = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$servico) {
                $erro = "Um dos serviços seleccionados não está disponível.";
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

                $stmt = $pdo->prepare("
                    INSERT INTO pedidos (cliente_id, utilizador_id, canal_origem, estado, valor_total)
                    VALUES (:cliente, :utilizador, :canal, 'Pendente', :total)
                ");
                
                $stmt->execute([
                    ":cliente"    => $cliente_id,
                    ":utilizador" => $_SESSION["utilizador_id"] ?? 1,
                    ":canal"      => $canal,
                    ":total"      => $total
                ]);

                $pedido_id = $pdo->lastInsertId();

               // Inserção dos itens do pedido para a tabela 'itens_pedido'
$stmt_item = $pdo->prepare("
    INSERT INTO itens_pedido (pedido_id, servico_id, quantidade, preco_aplicado, subtotal)
    VALUES (:pedido, :servico, :quantidade, :preco_aplicado, :subtotal)
");

foreach ($itens as $item) {
    $stmt_item->execute([
        ":pedido"         => $pedido_id,
        ":servico"        => $item["servico_id"],
        ":quantidade"     => $item["quantidade"],
        ":preco_aplicado" => $item["preco"],
        ":subtotal"       => $item["subtotal"]
    ]);
}

                $pdo->commit();
                header("Location: visualizar.php?id=" . $pedido_id);
                exit;

            } catch (PDOException $e) {
                $pdo->rollBack();
                $erro = "Não foi possível registar o pedido: " . $e->getMessage();
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
    <title>Novo Pedido — SIGIP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<main class="form-page">
    <div class="form-card pedido-card">
        <h1>Novo Pedido</h1>
        <p>Registar um novo pedido no sistema.</p>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="cliente_id">Cliente *</label>
                <select name="cliente_id" id="cliente_id" required>
                    <option value="">Seleccione um cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente["id"] ?>">
                            <?= htmlspecialchars($cliente["nome"]) ?> — <?= htmlspecialchars($cliente["telefone"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="canal_origem">Canal de origem *</label>
                <select name="canal_origem" id="canal_origem" required>
                    <option value="">Seleccione</option>
                    <option value="Presencial">Presencial</option>
                    <option value="WhatsApp">WhatsApp</option>
                    <option value="Email">Email</option>
                </select>
            </div>

            <h2>Serviços</h2>

            <div id="servicos-container">
                <div class="item-pedido">
                    <select name="servico_id[]" class="servico-select" required>
                        <option value="">Seleccione o serviço</option>
                        <?php foreach ($servicos as $servico): ?>
                            <option value="<?= $servico["id"] ?>" data-preco="<?= $servico["preco_unitario"] ?>">
                                <?= htmlspecialchars($servico["nome"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="number" name="quantidade[]" class="quantidade" min="1" step="1" placeholder="Quantidade" required>
                    <span class="subtotal">0,00 MT</span>
                </div>
            </div>

            <button type="button" id="adicionar-servico" class="btn-secondary">+ Adicionar serviço</button>

            <div class="total-pedido">
                Total: <strong id="total">0,00 MT</strong>
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Registar Pedido</button>
            </div>
        </form>
    </div>
</main>

<script src="../assets/js/script.js"></script>
</body>
</html>