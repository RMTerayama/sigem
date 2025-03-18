<?php
session_start();

function verificarPermissao($permissoesPermitidas) {
    // Se o usuário não estiver logado, redireciona para modalFalha.php
    if (!isset($_SESSION["usuario_autenticado"])) {
        header("Location: /sigem/modalFalha.php?erro=nao_autenticado");
        exit;
    }

    $usuario = $_SESSION["usuario_autenticado"];

    // Se o papel do usuário estiver na lista de permissões, ele pode acessar
    if (in_array($usuario["role"], $permissoesPermitidas)) {
        return true;
    }

    // Caso contrário, redireciona para modalFalha.php com erro de permissão
    header("Location: /sigem/modalFalha.php?erro=sem_permissao");
    exit;
}
?>
