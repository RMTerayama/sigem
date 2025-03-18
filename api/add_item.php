<?php
session_start();

include '../includes/conexao_sigem.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST["nome"];
    $modelo = $_POST["modelo"];
    $descricao = $_POST["descricao"];
    $quantidade = (int)$_POST["quantidade"];
    $patrimonio = (int)$_POST["patrimonio"];
    if ($patrimonio=== 0){
        $patrimonio='patrimonio';
    }else{
        $patrimonio='nserie';
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO item (nome, modelo, descricao, quantidade, identificacao,departamento_id) VALUES (?, ?, ?, ?, ?,?)");
        $stmt->execute([$nome, $modelo, $descricao, $quantidade, $patrimonio, $_SESSION["usuario_autenticado"]["departamento_id"]]);
        

        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método inválido"]);
}
?>
