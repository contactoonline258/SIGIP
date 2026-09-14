<?php
require_once "../includes/auth.php";
require_once "../config/conexao.php";

$pesquisa = trim($_GET["pesquisa"] ?? "");

if ($pesquisa !== "") {
    $sql = "SELECT id, nome, telefone, email, ativo, criado_em
            FROM clientes
            WHERE nome LIKE :pesquisa
               OR telefone LIKE :pesquisa
               OR email LIKE :pesquisa
            ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":pesquisa" => "%$pesquisa%"
    ]);
} else {
    $sql = "SELECT id, nome, telefone, email, ativo, criado_em
            FROM clientes
            ORDER BY id DESC";

    $stmt = $pdo->query($sql);
}

$clientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes — SIGIP</title>
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
        <a href="index.php">Clientes</a>
        <a href="../pedidos/index.php">Pedidos</a>
        <a href="../pagamentos/index.php">Pagamentos</a>
        <?php if ($_SESSION["perfil"] === "Administrador"): ?>
            <a href="../servicos/index.php">Serviços</a>
            <a href="../utilizadores/index.php">Utilizadores</a>
        <?php endif; ?>
    </aside>

    <main class="content">
        <div class="page-header">
            <div>
                <h1>Clientes</h1>
                <p>Gestão dos clientes da IMPRESSOS, E.I.</p>
            </div>
            <a href="criar.php" class="btn-primary btn-small">+ Novo Cliente</a>
        </div>

        <form method="GET" class="search-form">
            <input
                type="text"
                name="pesquisa"
                placeholder="Pesquisar por nome, telefone ou email"
                value="<?= htmlspecialchars($pesquisa) ?>"
            >
            <button type="submit" class="btn-primary btn-small">Pesquisar</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acções</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($clientes) > 0): ?>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?= $cliente["id"] ?></td>
                            <td><?= htmlspecialchars($cliente["nome"]) ?></td>
                            <td><?= htmlspecialchars($cliente["telefone"]) ?></td>
                            <td><?= htmlspecialchars($cliente["email"] ?? "") ?></td>
                            <td>
                                <?php if ($cliente["ativo"]): ?>
                                    <span class="status activo">Activo</span>
                                <?php else: ?>
                                    <span class="status inactivo">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="editar.php?id=<?= $cliente["id"] ?>" class="btn-action">Editar</a>
                                <?php if ($cliente["ativo"]): ?>
                                    <a
                                        href="desactivar.php?id=<?= $cliente["id"] ?>"
                                        class="btn-action danger"
                                        onclick="return confirm('Deseja desactivar este cliente?');"
                                    >
                                        Desactivar
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty">Nenhum cliente encontrado.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>