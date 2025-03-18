<?php
require __DIR__ . '/../config/jwt.php';
require __DIR__ . '/../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

session_start();

$erro_autenticacao = null;

if (!isset($_COOKIE['token'])) {
    $erro_autenticacao = "Acesso não autorizado. Faça login.";
} else {
    try {
        $usuario_autenticado = JWT::decode($_COOKIE['token'], new Key(JWT_SECRET, JWT_ALGO));

        // Armazena os dados do usuário na sessão
        $_SESSION["usuario_autenticado"] = [
            "id_user" => $usuario_autenticado->sub,
            "usuario" => $usuario_autenticado->usuario, // ✅ Agora usa 'usuario' em vez de 'name'
            "nome" => $usuario_autenticado->name,
            "role" => $usuario_autenticado->role,
            "departamento_id" => $usuario_autenticado->departamento_id
        ];

        // 🔹 Bloqueia "Coordenador" e "Usuário Padrão" de acessar dashboard.php
        if (in_array($_SESSION["usuario_autenticado"]["role"], ["coordenador", "padrao"]) 
            && basename($_SERVER['PHP_SELF']) === "dashboard.php") {
            header("Location: /sigem/termo.php");
            exit;
        }

    } catch (ExpiredException $e) {
        $erro_autenticacao = "Sua sessão expirou. Faça login novamente.";
    } catch (Exception $e) {
        $erro_autenticacao = "Token inválido.";
    }
}

// Se houver erro, redireciona para a página de bloqueio
if ($erro_autenticacao) {
    session_destroy();
    header("Location: /sigem/bloqueio.php?erro=nao_autenticado");
    exit;
}
?>
