<?php
session_start();
require_once "../includes/auth.php";
require_once "../config/conexao.php";

/*
|--------------------------------------------------------------------------
| Controlo de Acesso: Apenas Administrador
|--------------------------------------------------------------------------
*/
if ($_SESSION["perfil"] !== "Administrador") {
    header("Location: ../dashboard/index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Consulta dos Utilizadores
|--------------------------------------------------------------------------
*/
$stmt = $pdo->query("SELECT id, nome, email, perfil, ativo FROM utilizadores ORDER BY id DESC");
$utilizadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Mensagens de Feedback da Sessão
|--------------------------------------------------------------------------
*/
$mensagemSucesso = $_SESSION["sucesso"] ?? "";
$mensagemErro    = $_SESSION["erro"] ?? "";
unset($_SESSION["sucesso"], $_SESSION["erro"]);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilizadores — SIGIP</title>
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
            <a href="../servicos/index.php">Serviços</a>
            <a href="../pagamentos/index.php">Pagamentos</a>
            <a href="index.php" class="active">Utilizadores</a>
        </aside>

        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1>Gestão de Utilizadores</h1>
                <a href="novo.php" class="btn btn-primary" style="padding: 8px 15px; text-decoration: none; background: #007bff; color: #fff; border-radius: 4px;">+ Novo Utilizador</a>
            </div>

            <?php if ($mensagemSucesso): ?>
                <div style="color: green; margin-bottom: 15px; font-weight: bold;"><?= $mensagemSucesso ?></div>
            <?php endif; ?>

            <?php if ($mensagemErro): ?>
                <div style="color: red; margin-bottom: 15px; font-weight: bold;"><?= $mensagemErro ?></div>
            <?php endif; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Perfil</th>
                            <th>Estado</th>
                            <th style="width: 150px; text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($utilizadores)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">Nenhum utilizador registado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($utilizadores as $user): ?>
                                <tr>
                                    <td>#<?= $user["id"] ?></td>
                                    <td><?= htmlspecialchars($user["nome"]) ?></td>
                                    <td><?= htmlspecialchars($user["email"]) ?></td>
                                    <td><strong><?= htmlspecialchars($user["perfil"]) ?></strong></td>
                                    <td><?= $user["ativo"] ? "Ativo" : "Inativo" ?></td>
                                    <td style="text-align: center;">
                                        <a href="editar.php?id=<?= $user["id"] ?>" style="color: #007bff; text-decoration: none; margin-right: 10px;">Editar</a>
                                        
                                        <?php if (isset($_SESSION["utilizador_id"]) && $user["id"] == $_SESSION["utilizador_id"]): ?>
                                            <span style="color: #999;">(Você)</span>
                                        <?php else: ?>
                                            <a href="eliminar.php?id=<?= $user["id"] ?>" onclick="return confirm('Tem certeza que deseja eliminar o utilizador <?= htmlspecialchars($user['nome']) ?>?');" style="color: red; text-decoration: none;">Eliminar</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>