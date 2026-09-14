<?php
require_once "../includes/auth.php";
require_once "../config/conexao.php";
exigirAdministrador();

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("UPDATE servicos SET ativo = FALSE WHERE id = :id");
$stmt->execute([":id" => $id]);

header("Location: index.php");
exit;