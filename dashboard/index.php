<?php
session_start();
require_once "../includes/auth.php";
require_once "../config/conexao.php";

$nome   = $_SESSION["nome"];
$perfil = $_SESSION["perfil"];
$hoje   = date("Y-m-d");

/*
|--------------------------------------------------------------------------
| Consultas de Indicadores (KPIs)
|--------------------------------------------------------------------------
*/

// 1. Pedidos hoje
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE DATE(data_pedido) = ?");
$stmt->execute([$hoje]);
$pedidosHoje = $stmt->fetchColumn();

// 2. Receita hoje (Soma da coluna 'valor' na tabela pagamentos)
$stmt = $pdo->prepare("SELECT COALESCE(SUM(valor), 0) FROM pagamentos WHERE DATE(data_pagamento) = ?");
$stmt->execute([$hoje]);
$receitaHoje = $stmt->fetchColumn();

// 3. Pedidos Pendentes
$stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'Pendente'");
$pendentes = $stmt->fetchColumn();

// 4. Pedidos Em Produção
$stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'Em Produção'");
$emProducao = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| Consulta de Pedidos Recentes
|--------------------------------------------------------------------------
*/
$stmt = $pdo->query("
    SELECT
        p.id,
        c.nome AS cliente,
        p.valor_total,
        p.estado,
        p.data_pedido
    FROM pedidos p
    INNER JOIN clientes c ON c.id = p.cliente_id
    ORDER BY p.id DESC
    LIMIT 10
");
$pedidosRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SIGIP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="topbar">
        <img src="../assets/img/logo-impressos.png" alt="IMPRESSOS, E.I." class="logo">
        <div class="user-info">
            <span><?= htmlspecialchars($nome) ?></span>
            <span class="perfil">(<?= htmlspecialchars($perfil) ?>)</span>
            <a href="../auth/logout.php">Sair</a>
        </div>
    </header>

    <div class="layout">
        <aside class="sidebar">
            <a href="index.php" class="active">Dashboard</a>
            <a href="../clientes/index.php">Clientes</a>
            <a href="../pedidos/index.php">Pedidos</a>
            <?php if ($perfil === "Administrador"): ?>
                <a href="../servicos/index.php">Serviços</a>
                <a href="../pagamentos/index.php">Pagamentos</a>
                <a href="../utilizadores/index.php">Utilizadores</a>
            <?php else: ?>
                <a href="../pagamentos/index.php">Pagamentos</a>
            <?php endif; ?>
        </aside>

        <main class="content">
            <h1>Dashboard</h1>
            <p>Bem-vindo, <strong><?= htmlspecialchars($nome) ?></strong>.</p>

            <div class="cards">
                <div class="card">
                    <h3>Pedidos hoje</h3>
                    <p><?= $pedidosHoje ?></p>
                </div>
                <div class="card">
                    <h3>Receita hoje</h3>
                    <p><?= number_format($receitaHoje, 2, ",", ".") ?> MT</p>
                </div>
                <div class="card">
                    <h3>Pendentes</h3>
                    <p><?= $pendentes ?></p>
                </div>
                <div class="card">
                    <h3>Em produção</h3>
                    <p><?= $emProducao ?></p>
                </div>
            </div>

            <section style="margin-top: 30px;">
                <h2>Pedidos Recentes</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nº</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($pedidosRecentes)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center;">Nenhum pedido registado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pedidosRecentes as $pedido): ?>
                                <tr>
                                    <td>#<?= $pedido["id"] ?></td>
                                    <td><?= htmlspecialchars($pedido["cliente"]) ?></td>
                                    <td><?= number_format($pedido["valor_total"], 2, ",", ".") ?> MT</td>
                                    <td><?= htmlspecialchars($pedido["estado"]) ?></td>
                                    <td><?= date("d/m/Y H:i", strtotime($pedido["data_pedido"])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <?php if ($perfil === "Administrador"): ?>
    <section style="margin-top: 30px;">
        <h2>Relatórios</h2>
        <a href="../relatorios/vendas.php" class="btn btn-primary" target="_blank">
            Ver Relatório de Vendas
        </a>
    </section>
<?php endif; ?>
        </main>
    </div>
</body>
</html>