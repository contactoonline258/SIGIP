<?php
require_once "../includes/auth.php";
require_once "../config/conexao.php";

$sql = "SELECT 
            pg.id,
            pg.pedido_id,
            c.nome AS cliente,
            pg.valor,
            pg.metodo_pagamento,
            pg.data_pagamento,
            u.nome AS utilizador
        FROM pagamentos pg
        INNER JOIN pedidos p ON pg.pedido_id = p.id
        INNER JOIN clientes c ON p.cliente_id = c.id
        INNER JOIN utilizadores u ON pg.utilizador_id = u.id
        ORDER BY pg.id DESC";

$stmt = $pdo->query($sql);
$pagamentos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamentos — SIGIP</title>
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
        <a href="index.php">Pagamentos</a>
        <?php if ($_SESSION["perfil"] === "Administrador"): ?>
            <a href="../servicos/index.php">Serviços</a>
            <a href="../utilizadores/index.php">Utilizadores</a>
        <?php endif; ?>
    </aside>

    <main class="content">
        <div class="page-header">
            <div>
                <h1>Pagamentos</h1>
                <p>Histórico de pagamentos integrais recebidos.</p>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nº Pag.</th>
                        <th>Nº Pedido</th>
                        <th>Cliente</th>
                        <th>Método</th>
                        <th>Valor Pago</th>
                        <th>Data</th>
                        <th>Registado Por</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($pagamentos): ?>
                    <?php foreach ($pagamentos as $pag): ?>
                        <tr>
                            <td>#<?= sprintf('%04d', $pag["id"]) ?></td>
                            <td><a href="../pedidos/visualizar.php?id=<?= $pag["pedido_id"] ?>">#<?= sprintf('%04d', $pag["pedido_id"]) ?></a></td>
                            <td><?= htmlspecialchars($pag["cliente"]) ?></td>
                            <td><?= htmlspecialchars($pag["metodo_pagamento"]) ?></td>
                            <td><?= number_format($pag["valor"], 2, ",", ".") ?> MT</td>
                            <td><?= date("d/m/Y H:i", strtotime($pag["data_pagamento"])) ?></td>
                            <td><?= htmlspecialchars($pag["utilizador"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty">Nenhum pagamento registado.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>