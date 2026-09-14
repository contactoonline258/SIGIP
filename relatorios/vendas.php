<?php
session_start();
require_once "../includes/auth.php";
require_once "../config/conexao.php";

/*
|--------------------------------------------------------------------------
| Apenas Administrador
|--------------------------------------------------------------------------
*/
if ($_SESSION["perfil"] !== "Administrador") {
    die("Acesso não autorizado.");
}

/*
|--------------------------------------------------------------------------
| Buscar vendas
|--------------------------------------------------------------------------
*/
$stmt = $pdo->query("
    SELECT
        p.id AS pedido_id,
        c.nome AS cliente,
        pg.valor,
        pg.metodo_pagamento,
        pg.data_pagamento
    FROM pagamentos pg
    INNER JOIN pedidos p ON p.id = pg.pedido_id
    INNER JOIN clientes c ON c.id = p.cliente_id
    ORDER BY pg.data_pagamento DESC
");
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Total de vendas
|--------------------------------------------------------------------------
*/
$totalVendas = 0;
foreach ($vendas as $venda) {
    $totalVendas += (float) $venda["valor"];
}

$dataEmissao = date("d/m/Y H:i");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Vendas — SIGIP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 30px;
        }
        .cabecalho {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .cabecalho h1 { margin: 0; font-size: 22px; }
        .cabecalho h2 { margin: 5px 0; font-size: 15px; color: #555; }
        .info { margin-bottom: 15px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            text-align: left;
        }
        th { background-color: #f4f4f4; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .total {
            margin-top: 20px;
            text-align: right;
            font-size: 16px;
            font-weight: bold;
        }
        .botoes {
            margin-bottom: 20px;
            text-align: right;
        }
        .btn {
            padding: 8px 15px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        /* Oculta os botões no momento da impressão/guardar em PDF */
        @media print {
            .botoes { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

    <div class="botoes">
        <button onclick="window.print()" class="btn">Imprimir / Guardar em PDF</button>
        <a href="../dashboard/index.php" class="btn" style="background-color: #6c757d;">Voltar</a>
    </div>

    <div class="cabecalho">
        <h1>IMPRESSOS, E.I.</h1>
        <h2>SIGIP — Sistema Web de Gestão da IMPRESSOS</h2>
        <h3>RELATÓRIO DE VENDAS</h3>
    </div>

    <div class="info">
        <strong>Data de emissão:</strong> <?= $dataEmissao ?>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 10%;">Nº Pedido</th>
                <th>Cliente</th>
                <th style="width: 20%;">Método</th>
                <th class="text-center" style="width: 20%;">Data</th>
                <th class="text-right" style="width: 20%;">Valor</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($vendas)): ?>
            <tr>
                <td colspan="5" class="text-center">Nenhuma venda registada.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($vendas as $venda): ?>
                <tr>
                    <td class="text-center">#<?= htmlspecialchars($venda["pedido_id"]) ?></td>
                    <td><?= htmlspecialchars($venda["cliente"]) ?></td>
                    <td><?= htmlspecialchars($venda["metodo_pagamento"]) ?></td>
                    <td class="text-center"><?= date("d/m/Y H:i", strtotime($venda["data_pagamento"])) ?></td>
                    <td class="text-right"><?= number_format($venda["valor"], 2, ",", ".") ?> MT</td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="total">
        TOTAL DE VENDAS: <?= number_format($totalVendas, 2, ",", ".") ?> MT
    </div>

</body>
</html>