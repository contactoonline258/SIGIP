<?php
require_once "../includes/auth.php";
require_once "../config/conexao.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}

// Procura os detalhes do pedido e cliente
$stmt = $pdo->prepare("
    SELECT p.*, c.nome AS cliente_nome, c.telefone AS cliente_telefone, c.email AS cliente_email
    FROM pedidos p
    INNER JOIN clientes c ON p.cliente_id = c.id
    WHERE p.id = :id LIMIT 1
");
$stmt->execute([":id" => $id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    header("Location: index.php");
    exit;
}

// Procura os itens do pedido
$stmt_itens = $pdo->prepare("
    SELECT ip.*, s.nome AS servico_nome
    FROM itens_pedido ip
    INNER JOIN servicos s ON ip.servico_id = s.id
    WHERE ip.pedido_id = :id
");
$stmt_itens->execute([":id" => $id]);
$itens = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);

$erro_msg = "";
if (isset($_GET["erro"])) {
    if ($_GET["erro"] === "transicao_invalida") $erro_msg = "Mudança de estado não permitida para o estado atual.";
    if ($_GET["erro"] === "motivo_obrigatorio") $erro_msg = "É obrigatório indicar o motivo do cancelamento.";
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #<?= $pedido["id"] ?> — SIGIP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="topbar">
    <img src="../assets/img/logo-impressos.png" alt="IMPRESSOS, E.I." class="logo">
    <div class="user-info">
        <span><?= htmlspecialchars($_SESSION["nome"] ?? 'Utilizador') ?></span>
        <a href="../auth/logout.php">Sair</a>
    </div>
</header>

<div class="layout">
    <aside class="sidebar">
        <a href="../dashboard/index.php">Dashboard</a>
        <a href="../clientes/index.php">Clientes</a>
        <a href="index.php" class="active">Pedidos</a>
        <a href="../pagamentos/index.php">Pagamentos</a>
    </aside>

    <main class="content">
        <?php if ($erro_msg): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro_msg) ?></div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h1>Pedido #<?= sprintf("%04d", $pedido["id"]) ?></h1>
                <p>Data: <?= date("d/m/Y H:i", strtotime($pedido["data_pedido"])) ?></p>
            </div>
            <div>
                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $pedido["estado"])) ?>">
                    <?= htmlspecialchars($pedido["estado"]) ?>
                </span>
            </div>
        </div>

        <div class="grid-2">
            <div class="card">
                <h3>Dados do Cliente</h3>
                <p><strong>Nome:</strong> <?= htmlspecialchars($pedido["cliente_nome"]) ?></p>
                <p><strong>Telefone:</strong> <?= htmlspecialchars($pedido["cliente_telefone"]) ?></p>
                <p><strong>Canal:</strong> <?= htmlspecialchars($pedido["canal_origem"]) ?></p>
            </div>

            <div class="card">
                <h3>Ações do Pedido</h3>
                
                <?php if ($pedido["estado"] === "Pendente"): ?>
                    <a href="../pagamentos/registar.php?pedido_id=<?= $pedido["id"] ?>" class="btn-primary">
                        Registar Pagamento
                    </a>
                <?php endif; ?>

                <?php if ($pedido["estado"] === "Em Produção"): ?>
                    <form action="atualizar_estado.php" method="POST" style="display:inline;">
                        <input type="hidden" name="pedido_id" value="<?= $pedido["id"] ?>">
                        <input type="hidden" name="novo_estado" value="Concluído">
                        <button type="submit" class="btn-success">Marcar como Concluído</button>
                    </form>
                <?php endif; ?>

                <?php if ($pedido["estado"] === "Concluído"): ?>
                    <form action="atualizar_estado.php" method="POST" style="display:inline;">
                        <input type="hidden" name="pedido_id" value="<?= $pedido["id"] ?>">
                        <input type="hidden" name="novo_estado" value="Entregue">
                        <button type="submit" class="btn-primary">Registar Entrega ao Cliente</button>
                    </form>
                <?php endif; ?>

                <?php if (in_array($pedido["estado"], ["Pendente", "Em Produção"], true)): ?>
                    <button type="button" class="btn-danger" onclick="document.getElementById('modal-cancelar').style.display='block'">
                        Cancelar Pedido
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($pedido["estado"] === "Cancelado"): ?>
            <div class="alert alert-danger" style="margin-top: 20px;">
                <strong>Motivo do Cancelamento:</strong> <?= htmlspecialchars($pedido["motivo_cancelamento"] ?? 'Não especificado') ?>
            </div>
        <?php endif; ?>

        <div class="table-container" style="margin-top:20px;">
            <h3>Itens do Pedido</h3>
            <table>
                <thead>
                    <tr>
                        <th>Serviço</th>
                        <th>Quantidade</th>
                        <th>Preço Unit.</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item["servico_nome"]) ?></td>
                            <td><?= $item["quantidade"] ?></td>
                            <td><?= number_format($item["preco_aplicado"], 2, ",", ".") ?> MT</td>
                            <td><?= number_format($item["subtotal"], 2, ",", ".") ?> MT</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" style="text-align:right;">Total:</th>
                        <th><?= number_format($pedido["valor_total"], 2, ",", ".") ?> MT</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </main>
</div>

<!-- Modal de Cancelamento -->
<div id="modal-cancelar" class="modal" style="display:none;">
    <div class="modal-content">
        <h3>Cancelar Pedido #<?= $pedido["id"] ?></h3>
        <form action="atualizar_estado.php" method="POST">
            <input type="hidden" name="pedido_id" value="<?= $pedido["id"] ?>">
            <input type="hidden" name="novo_estado" value="Cancelado">
            
            <div class="form-group">
                <label for="motivo_cancelamento">Motivo do Cancelamento *</label>
                <textarea name="motivo_cancelamento" id="motivo_cancelamento" required placeholder="Ex: Desistência do cliente / Erro na especificação"></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="document.getElementById('modal-cancelar').style.display='none'">Voltar</button>
                <button type="submit" class="btn-danger">Confirmar Cancelamento</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>