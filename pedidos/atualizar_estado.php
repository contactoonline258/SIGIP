<?php
require_once "../includes/auth.php";
require_once "../config/conexao.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$pedido_id     = filter_input(INPUT_POST, "pedido_id", FILTER_VALIDATE_INT);
$novo_estado   = $_POST["novo_estado"] ?? "";
$motivo_canc   = trim($_POST["motivo_cancelamento"] ?? "");

if (!$pedido_id || empty($novo_estado)) {
    header("Location: index.php");
    exit;
}

// Carrega o estado atual do pedido
$stmt = $pdo->prepare("SELECT estado FROM pedidos WHERE id = :id LIMIT 1");
$stmt->execute([":id" => $pedido_id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    header("Location: index.php");
    exit;
}

$estado_atual = $pedido["estado"];

// Matriz de transições permitidas
$transicoes_validas = [
    "Pendente"    => ["Cancelado"],
    "Em Produção" => ["Concluído", "Cancelado"],
    "Concluído"   => ["Entregue"]
];

if (!isset($transicoes_validas[$estado_atual]) || !in_array($novo_estado, $transicoes_validas[$estado_atual], true)) {
    header("Location: visualizar.php?id=" . $pedido_id . "&erro=transicao_invalida");
    exit;
}

// Validação extra: Cancelamento exige motivo
if ($novo_estado === "Cancelado" && empty($motivo_canc)) {
    header("Location: visualizar.php?id=" . $pedido_id . "&erro=motivo_obrigatorio");
    exit;
}

try {
    if ($novo_estado === "Cancelado") {
        $stmt_update = $pdo->prepare("
            UPDATE pedidos 
            SET estado = :estado, motivo_cancelamento = :motivo 
            WHERE id = :id
        ");
        $stmt_update->execute([
            ":estado" => $novo_estado,
            ":motivo" => $motivo_canc,
            ":id"     => $pedido_id
        ]);
    } else {
        $stmt_update = $pdo->prepare("
            UPDATE pedidos 
            SET estado = :estado 
            WHERE id = :id
        ");
        $stmt_update->execute([
            ":estado" => $novo_estado,
            ":id"     => $pedido_id
        ]);
    }

    header("Location: visualizar.php?id=" . $pedido_id . "&sucesso=1");
    exit;

} catch (PDOException $e) {
    header("Location: visualizar.php?id=" . $pedido_id . "&erro=bd");
    exit;
}