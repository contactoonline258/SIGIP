<?php
session_start();
require_once "../includes/auth.php";
require_once "../config/conexao.php";

if ($_SESSION["perfil"] !== "Administrador") {
    header("Location: ../dashboard/index.php");
    exit;
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($id) {
    // Bloquear a eliminação do próprio utilizador logado
    if ($id == $_SESSION["usuario_id"]) {
        $_SESSION["erro"] = "Não pode eliminar a sua própria conta enquanto estiver com a sessão iniciada.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM utilizadores WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION["sucesso"] = "Utilizador eliminado com sucesso!";
        } else {
            $_SESSION["erro"] = "Erro ao eliminar utilizador.";
        }
    }
}

header("Location: index.php");
exit;