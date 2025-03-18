<?php
session_start();

// Habilitar a exibição de erros para facilitar o debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Verifica se o usuário está autenticado
if (!isset($_SESSION["usuario_autenticado"]['id_user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit;
}

// Inclui a conexão com o banco de dados
include '../includes/conexao_sigem.php';

// Recebe os dados via POST
$id_item = isset($_POST['id_item']) ? intval($_POST['id_item']) : null;
$quantidade = isset($_POST['quantidadedescremento']) ? intval($_POST['quantidadedescremento']) : null;
$justificativa = isset($_POST['justificativa']) ? trim($_POST['justificativa']) : null;

// Validação dos dados recebidos
if (!$id_item || !$quantidade || !$justificativa) {
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
    exit;
}

// Obtém o ID do usuário autenticado
$usuario_id = $_SESSION["usuario_autenticado"]['id_user'];

// Captura a data e hora atuais
$data_atual = date('Y-m-d H:i:s');

try {
    // Verifica se o item existe e pega a quantidade atual
    $stmt = $pdo->prepare("SELECT quantidade FROM item WHERE id_item = :id_item");
    $stmt->bindParam(':id_item', $id_item, PDO::PARAM_INT);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo json_encode(['status' => 'error', 'message' => 'Item não encontrado.']);
        exit;
    }

    // Verifica se a quantidade a ser decrementada não é maior que a quantidade atual
    if ($quantidade > $item['quantidade']) {
        echo json_encode(['status' => 'error', 'message' => 'Quantidade a ser decrementada é maior que a quantidade disponível.']);
        exit;
    }

    // Atualiza a quantidade do item no banco de dados
    $nova_quantidade = $item['quantidade'] - $quantidade;

    // Garantir que a nova quantidade não seja negativa
    if ($nova_quantidade < 0) {
        echo json_encode(['status' => 'error', 'message' => 'Quantidade resultante não pode ser negativa.']);
        exit;
    }

    // Atualiza a quantidade do item
    $stmt = $pdo->prepare("UPDATE item SET quantidade = :quantidade WHERE id_item = :id_item");
    $stmt->bindParam(':quantidade', $nova_quantidade, PDO::PARAM_INT);
    $stmt->bindParam(':id_item', $id_item, PDO::PARAM_INT);

    if (!$stmt->execute()) {
        throw new Exception('Erro ao atualizar a quantidade do item.');
    }

    // Registra a alteração na tabela alteracoes_itens
    $stmt = $pdo->prepare("INSERT INTO alteracoes_itens (id_item, acao, usuario_id, data_alteracao, quantidade, justificativa) 
    VALUES (:id_item, :acao, :usuario_id, :data_alteracao, :quantidade, :justificativa)");

    $stmt->execute([
        ':id_item' => $id_item,
        ':acao' => 'decremento',
        ':usuario_id' => $usuario_id,
        ':data_alteracao' => $data_atual,
        ':quantidade' => $quantidade,
        ':justificativa' => $justificativa
    ]);

    echo json_encode(['status' => 'success', 'nova_quantidade' => $nova_quantidade]);

} catch (Exception $e) {
    // Se ocorrer algum erro, captura a mensagem e retorna para o frontend
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
exit;
?>
