<?php

session_start();

require_once "../config/conexao.php";
require_once "../includes/auth.php";

$erro = "";
$sucesso = "";

$pedido_id = intval($_GET["pedido_id"] ?? 0);

if ($pedido_id <= 0) {
    die("Pedido inválido.");
}

/*
|--------------------------------------------------------------------------
| Buscar pedido
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        p.id,
        p.valor_total,
        p.estado,
        c.nome AS cliente_nome
    FROM pedidos p
    INNER JOIN clientes c
        ON c.id = p.cliente_id
    WHERE p.id = ?
    LIMIT 1
");

$stmt->execute([$pedido_id]);

$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    die("Pedido não encontrado.");
}

/*
|--------------------------------------------------------------------------
| Verificar estado do pedido
|--------------------------------------------------------------------------
*/

if ($pedido["estado"] !== "Pendente") {
    die("Este pedido não está disponível para pagamento.");
}

/*
|--------------------------------------------------------------------------
| Registar pagamento
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $metodo = trim($_POST["metodo"] ?? "");
    $valor_pago = floatval($_POST["valor_pago"] ?? 0);

    /*
    |----------------------------------------------------------------------
    | Validar método
    |----------------------------------------------------------------------
    */

    $metodos_validos = [
        "Numerário",
        "M-Pesa",
        "POS / Cartão"
    ];

    if (!in_array($metodo, $metodos_validos, true)) {
        $erro = "Seleccione um método de pagamento válido.";
    }

    /*
    |----------------------------------------------------------------------
    | Validar valor
    |----------------------------------------------------------------------
    */

    elseif ($valor_pago <= 0) {
        $erro = "O valor pago deve ser maior que zero.";
    }

    elseif (abs($valor_pago - $pedido["valor_total"]) > 0.01) {
        $erro = "O pagamento deve corresponder ao valor total do pedido.";
    }

    else {
        try {
            $pdo->beginTransaction();

       // Captura o ID do utilizador da sessão activa
$utilizador_id = $_SESSION["utilizador_id"] ?? 1;

/*
|------------------------------------------------------------------
| Inserir pagamento (Incluindo utilizador_id)
|------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    INSERT INTO pagamentos
    (
        pedido_id,
        utilizador_id,
        valor,
        metodo_pagamento,
        data_pagamento
    )
    VALUES (?, ?, ?, ?, NOW())
");

$stmt->execute([
    $pedido_id,
    $utilizador_id,
    $valor_pago,
    $metodo
]);

            /*
            |------------------------------------------------------------------
            | Alterar estado do pedido
            |------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                UPDATE pedidos
                SET estado = 'Em Produção'
                WHERE id = ?
                AND estado = 'Pendente'
            ");

            $stmt->execute([
                $pedido_id
            ]);

            $pdo->commit();

            header(
                "Location: ../pedidos/visualizar.php?id=" .
                $pedido_id .
                "&pagamento=sucesso"
            );

            exit;

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            // Exibir a mensagem real da excepção em caso de erro no MySQL
            $erro = "Não foi possível registar o pagamento: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registar Pagamento - SIGIP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="container">

    <h1>Registar Pagamento</h1>

    <p>
        <strong>Pedido Nº:</strong>
        <?= htmlspecialchars($pedido["id"]) ?>
    </p>

    <p>
        <strong>Cliente:</strong>
        <?= htmlspecialchars($pedido["cliente_nome"]) ?>
    </p>

    <p>
        <strong>Total a pagar:</strong>
        <?= number_format(
            $pedido["valor_total"],
            2,
            ",",
            "."
        ) ?> MT
    </p>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label for="valor_pago">Valor pago</label>
            <input
                type="number"
                id="valor_pago"
                name="valor_pago"
                value="<?= htmlspecialchars($pedido["valor_total"]) ?>"
                step="0.01"
                min="0"
                required
            >
        </div>

        <div class="form-group">
            <label for="metodo">Método de pagamento</label>
            <select id="metodo" name="metodo" required>
                <option value="">Seleccione</option>
                <option value="Numerário">Numerário</option>
                <option value="M-Pesa">M-Pesa</option>
                <option value="POS / Cartão">POS / Cartão</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            Confirmar Pagamento
        </button>

        <a href="../pedidos/visualizar.php?id=<?= $pedido_id ?>" class="btn">
            Cancelar
        </a>

    </form>

</div>

</body>

</html>