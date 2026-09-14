<?php
require_once "../includes/auth.php";
require_once "../config/conexao.php";
exigirAdministrador();

$sql = "SELECT * FROM servicos ORDER BY id DESC";
$stmt = $pdo->query($sql);
$servicos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços — SIGIP</title>
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
        <a href="index.php">Serviços</a>
        <a href="../pagamentos/index.php">Pagamentos</a>
        <a href="../utilizadores/index.php">Utilizadores</a>
    </aside>

    <main class="content">
        <div class="page-header">
            <div>
                <h1>Serviços</h1>
                <p>Serviços e preços da IMPRESSOS, E.I.</p>
            </div>
            <a href="criar.php" class="btn-primary btn-small">+ Novo Serviço</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Serviço</th>
                        <th>Descrição</th>
                        <th>Preço Base</th>
                        <th>Estado</th>
                        <th>Acções</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($servicos as $servico): ?>
                    <tr>
                        <td><?= $servico["id"] ?></td>
                        <td><?= htmlspecialchars($servico["nome"]) ?></td>
                        <td><?= htmlspecialchars($servico["descricao"] ?? "") ?></td>
                        <td>
                            <?php if ($servico["nome"] === "Encadernação"): ?>
                                <em>Por escalão (30 MT / 50 fls)</em>
                            <?php else: ?>
                                <?= number_format($servico["preco_unitario"], 2, ",", ".") ?> MT
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($servico["ativo"]): ?>
                                <span class="status activo">Activo</span>
                            <?php else: ?>
                                <span class="status inactivo">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="editar.php?id=<?= $servico["id"] ?>" class="btn-action">Editar</a>
                            <?php if ($servico["ativo"]): ?>
                                <a
                                    href="desactivar.php?id=<?= $servico["id"] ?>"
                                    class="btn-action danger"
                                    onclick="return confirm('Deseja desactivar este serviço?');"
                                >
                                    Desactivar
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>