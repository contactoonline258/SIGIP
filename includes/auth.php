<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["utilizador_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

function exigirAdministrador() {
    if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] !== "Administrador") {
        http_response_code(403);
        exit("Acesso não autorizado.");
    }
}