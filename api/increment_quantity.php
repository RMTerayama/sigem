<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

include '../includes/conexao_sigem.php';

// Verifica se os dados foram enviados corretamente
$id_item = isset($_POST['id_item']) ? intval($_POST['id_item']) : null;
$increment_quantity = isset($_POST['increment_quantity']) ? intval($_POST['increment_quantity']) : null;

// Validação dos dados recebidos
if (!$id_item || !$increment_quantity || $increment_quantity <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
    exit;
}

// Verifica se o usuário está autenticado
if (!isset($_SESSION["usuario_autenticado"]['id_user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit;
}

try {
    // Seleciona a quantidade atual do item
    $stmt = $pdo->prepare("SELECT quantidade FROM item WHERE id_item = :id_item");
    $stmt->bindParam(':id_item', $id_item, PDO::PARAM_INT);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo json_encode(['status' => 'error', 'message' => 'Item não encontrado.']);
        exit;
    }

    // Atualiza a quantidade no banco
    $nova_quantidade = $item['quantidade'] + $increment_quantity;

    $stmt = $pdo->prepare("UPDATE item SET quantidade = :quantidade WHERE id_item = :id_item");
    $stmt->bindParam(':quantidade', $nova_quantidade, PDO::PARAM_INT);
    $stmt->bindParam(':id_item', $id_item, PDO::PARAM_INT);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao atualizar a quantidade do item.');
    }

    // Registra a alteração na tabela `alteracoes_itens`
    $data_atual = date('Y-m-d H:i:s'); 

    $stmt = $pdo->prepare("INSERT INTO alteracoes_itens (id_item, acao, usuario_id, data_alteracao, quantidade) 
    VALUES (:id_item, :acao, :usuario_id, :data_alteracao, :quantidade)");

    $stmt->execute([
        ':id_item' => $id_item,
        ':acao' => 'incremento',
        ':usuario_id' => $_SESSION["usuario_autenticado"]['id_user'],
        ':data_alteracao' => $data_atual,
        ':quantidade' => $increment_quantity
    ]);

    echo json_encode(['status' => 'success', 'nova_quantidade' => $nova_quantidade]);

} catch (Exception $e) {
    error_log("Erro: " . $e->getMessage()); // Registra no log do servidor
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
exit;
?>
