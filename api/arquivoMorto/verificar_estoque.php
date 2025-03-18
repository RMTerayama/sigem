<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require '../includes/conexoes.php'; 

try {
    $itemId = $_GET['item_id'] ?? null;
    $quantidadeSolicitada = $_GET['quantidade'] ?? null;

    if (!$itemId || !$quantidadeSolicitada) {
        throw new Exception("Dados inválidos.");
    }

    $stmt = $pdo->prepare("SELECT quantidade FROM item WHERE id = :item_id");
    $stmt->execute([':item_id' => $itemId]);
    $estoqueAtual = $stmt->fetchColumn();

    if ($estoqueAtual === false) {
        throw new Exception("Item não encontrado no estoque.");
    }

    if ($quantidadeSolicitada > $estoqueAtual) {
        throw new Exception("Quantidade solicitada excede o estoque disponível.");
    }

    echo json_encode(["sucesso" => true, "mensagem" => "Estoque disponível."]);

} catch (Exception $e) {
    echo json_encode(["sucesso" => false, "mensagem" => $e->getMessage()]);
}
?>