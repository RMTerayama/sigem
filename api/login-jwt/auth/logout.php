<?php
session_start(); // Inicia a sessão para garantir que ela possa ser destruída

// Apaga o token da sessão e do cookie
unset($_SESSION["token"]);
setcookie("token", "", time() - 3600, "/", "", false, true); // Remove o token

//Adicionado por Murilo, da linha 8 até a 14. Motivo: após logout, a sessão deve ter seus dados apagados para ser de fato destruída (quiestão de boas práticas)

// Apagar todas as variáveis de sessão
session_unset();

// Destruir a sessão
session_destroy();

// Redireciona para a página de login ANTES de qualquer saída
header("Location: ../../../login.php");
exit; // Garante que o script pare de executar
?>
