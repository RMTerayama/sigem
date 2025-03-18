<?php
include '../includes/conexao_sigem.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_item = $_POST['id_item'];
    $nome = $_POST['nome'];
    $modelo = $_POST['modelo'];
    $descricao = $_POST['descricao'];

    try {
        $stmt = $pdo->prepare("UPDATE item SET nome = ?, modelo = ?, descricao = ? WHERE id_item = ?");
        $stmt->execute([$nome, $modelo, $descricao, $id_item]);

        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>
