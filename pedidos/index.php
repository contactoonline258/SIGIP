<?php
require_once "../includes/auth.php";
require_once "../config/conexao.php";

$sql = "SELECT
            p.id,
            c.nome AS cliente,
            u.nome AS utilizador,
            p.canal_origem,
            p.estado,
            p.valor_total,
            p.data_pedido
        FROM pedidos p
        INNER JOIN clientes c ON p.cliente_id = c.id
        INNER JOIN utilizadores u ON p.utilizador_id = u.id
        ORDER BY p.id DESC";

$stmt = $pdo->query($sql);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos — SIGIP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="topbar">
    <img src="../assets/img/logo-impressos.png" alt="IMPRESSOS, E.I." class="logo">
    <div class="user-info">
        <span><?= htmlspecialchars($_SESSION["nome"] ?? 'Utilizador') ?></span>
        <span class="perfil"><?= htmlspecialchars($_SESSION["perfil"] ?? '') ?></span>
        <a href="../auth/logout.php">Sair</a>
    </div>
</header>

<div class="layout">
    <aside class="sidebar">
        <a href="../dashboard/index.php">Dashboard</a>
        <a href="../clientes/index.php">Clientes</a>
        <a href="index.php" class="active">Pedidos</a>
        <a href="../pagamentos/index.php">Pagamentos</a>
        <?php if (isset($_SESSION["perfil"]) && $_SESSION["perfil"] === "Administrador"): ?>
            <a href="../servicos/index.php">Serviços</a>
            <a href="../utilizadores/index.php">Utilizadores</a>
        <?php endif; ?>
    </aside>

    <main class="content">
        <div class="page-header">
            <div>
                <h1>Pedidos</h1>
                <p>Gestão dos pedidos da IMPRESSOS, E.I.</p>
            </div>
            <a href="criar.php" class="btn-primary btn-small">+ Novo Pedido</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Cliente</th>
                        <th>Canal</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Data</th>
                        <th>Acções</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($pedidos): ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td>#<?= $pedido["id"] ?></td>
                            <td><?= htmlspecialchars($pedido["cliente"]) ?></td>
                            <td><?= htmlspecialchars($pedido["canal_origem"]) ?></td>
                            <td><?= number_format($pedido["valor_total"], 2, ",", ".") ?> MT</td>
                            <td>
                                <span class="status status-<?= strtolower(str_replace(' ', '-', $pedido["estado"])) ?>">
                                    <?= htmlspecialchars($pedido["estado"]) ?>
                                </span>
                            </td>
                            <td><?= date("d/m/Y H:i", strtotime($pedido["data_pedido"])) ?></td>
                            <td>
                                <a href="visualizar.php?id=<?= $pedido["id"] ?>" class="btn-action">Ver</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty">Nenhum pedido registado.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>