<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json'); // Força a resposta JSON
ob_start(); // Inicia o buffer de saída para evitar HTML inesperado

session_start(); 
require '../includes/conexoes.php';


try {
    $pdo->beginTransaction(); // Inicia a transação

    // 1️⃣ Salvar o termo
    $stmt = $pdo->prepare("INSERT INTO db_sigem.termos_saida (numero_termo, secretaria, departamento, responsavel, destinatario, departamento_responsavel) 
    VALUES (:numero_termo, :secretaria, :departamento, :responsavel, :destinatario, :departamento_responsavel)");


    $numeroTermo = $ultimo_id+1;
    //$numeroTermo = "TERMO-" . date('Y') . $pdo->lastInsertId();
    $stmt->execute([
        ':numero_termo' => $numeroTermo,
        ':secretaria'   => $_POST['secretaria'] ?? null,
        ':departamento' => $_POST['departamento'] ?? null,
        ':responsavel'  => $_SESSION["usuario_autenticado"]["nome"] ?? null,
        ':destinatario' => $_POST['destinatario'] ?? null,
        ':departamento_responsavel' => $_SESSION["usuario_autenticado"]["departamento_id"]

    ]);

    $termoId = $pdo->lastInsertId();

    if (!$termoId) {
        throw new Exception("Erro ao obter ID do termo.");
    }

    // 2️⃣ Verificar se itens foram enviados
    if (!isset($_POST['itens_saida']) || empty($_POST['itens_saida'])) {
        throw new Exception("Nenhum item foi enviado para salvar.");
    }

    // Decodifica os itens enviados como JSON
    $itensSaida = json_decode($_POST['itens_saida'], true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Erro ao decodificar itens: " . json_last_error_msg());
    }

    // 3️⃣ Preparar a inserção de itens e atualização do estoque
    $stmtItem = $pdo->prepare("INSERT INTO db_sigem.itens_saida (termo_id, item_id, quantidade, identificacao) 
                               VALUES (:termo_id, :item_id, :quantidade, :identificacao)");

    $stmtCheckEstoque = $pdo->prepare("SELECT quantidade FROM db_sigem.item WHERE id_item = :item_id");
    $stmtUpdateEstoque = $pdo->prepare("UPDATE db_sigem.item SET quantidade = quantidade - :quantidade WHERE id_item = :item_id");
    $stmtGetNomeItem = $pdo->prepare("SELECT nome FROM db_sigem.item WHERE id_item = :item_id");


    foreach ($itensSaida as $item) {
        if (!isset($item['id']) || !isset($item['quantidade']) || empty($item['id'])) {
            throw new Exception("Dados de item inválidos: " . print_r($item, true));
        }

        $itemId = $item['id'];
        $quantidadeSolicitada = $item['quantidade'];

        // 4️⃣ Verificar estoque disponível
        $stmtCheckEstoque->execute([':item_id' => $itemId]);
        $estoqueAtual = $stmtCheckEstoque->fetchColumn();

        //Buscar o nome do item
        $stmtGetNomeItem->execute([':item_id' => $itemId]);
        $nomeItem = $stmtGetNomeItem->fetchColumn(); // Obtém apenas o nome do item

        if ($estoqueAtual === false) {
            throw new Exception("Item ID $itemId não encontrado no estoque.");
        }

        
        if ($quantidadeSolicitada > $estoqueAtual) {
            throw new Exception("Quantidade solicitada ($quantidadeSolicitada) para o item $nomeItem excede o estoque disponível ($estoqueAtual). O termo não será criado.");
        }


        // 5️⃣ Inserir item na tabela itens_saida
        $stmtItem->execute([
            ':termo_id'      => $termoId,
            ':item_id'       => $itemId,
            ':quantidade'    => $quantidadeSolicitada,
            ':identificacao' => is_array($item['identificacao']) ? implode(',', $item['identificacao']) : $item['identificacao'],
        ]);

        // 6️⃣ Atualizar o estoque na tabela item
        $stmtUpdateEstoque->execute([
            ':quantidade' => $quantidadeSolicitada,
            ':item_id'    => $itemId
        ]);
    }

    $pdo->commit(); // Confirma a transação

    // Captura qualquer saída inesperada antes do JSON e impede erros visíveis no frontend
    $buffer = ob_get_clean(); 
    if (!empty($buffer)) {
        error_log("⚠️ Aviso: Saída inesperada antes do JSON: " . $buffer);
    }

    echo json_encode(["sucesso" => true, "mensagem" => "Termo salvo com sucesso!"]);

} catch (Exception $e) {
    $pdo->rollBack(); // Desfaz alterações em caso de erro

    // Log de erro
    error_log("Erro ao salvar termo: " . $e->getMessage());

    // Retorna erro para o frontend
    echo json_encode([
        "sucesso" => false, 
        "mensagem" => "Erro ao salvar: " . $e->getMessage()
    ]);
}
?>
