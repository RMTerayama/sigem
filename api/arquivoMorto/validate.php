<?php
require __DIR__ . '/../config/jwt.php';
require __DIR__ . '/../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

session_start(); // Inicia a sessão

$erro_autenticacao = null; // Variável para armazenar erros

if (!isset($_COOKIE['token'])) {
    $erro_autenticacao = "Acesso não autorizado. Faça login.";
} else {
    try {
        $usuario_autenticado = JWT::decode($_COOKIE['token'], new Key(JWT_SECRET, JWT_ALGO));
        $_SESSION["usuario_autenticado"] = $usuario_autenticado;
    } catch (ExpiredException $e) {
        $erro_autenticacao = "Sua sessão expirou. Faça login novamente.";
    } catch (Exception $e) {
        $erro_autenticacao = "Token inválido.";
    }
}
?>
