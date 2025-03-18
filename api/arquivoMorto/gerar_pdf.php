<?php
require '../vendor/autoload.php'; // Autoload do Composer
require '../includes/conexoes.php';

// Verifica se o ID foi passado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die(json_encode(["error" => "ID do termo de entrega não informado."]));
}

$termo_id = $_GET['id'];

try {
    // Consulta os dados do termo de entrega
    $sql_termo = "
        SELECT 
            ts.id, 
            ts.numero_termo, 
            s.nome AS secretaria_nome, 
            d.nome AS departamento_nome, 
            ts.responsavel, 
            ts.data_saida,
            ts.assinatura
        FROM 
            db_sigem.termos_saida ts
        INNER JOIN 
            db_pmtl.secretarias s ON ts.secretaria = s.id
        INNER JOIN 
            db_pmtl.departamentos d ON ts.departamento = d.id
        WHERE 
            ts.id = :id
    ";
    $stmt_termo = $pdo->prepare($sql_termo);
    $stmt_termo->execute([':id' => $termo_id]);
    $termo = $stmt_termo->fetch(PDO::FETCH_ASSOC);

    if (!$termo) {
        die(json_encode(["error" => "Termo de entrega não encontrado."]));
    }


    // Consulta os itens relacionados ao termo de entrega
    $sql_itens_saida = "
        SELECT 
            i.nome AS item_nome, 
            i.modelo AS item_modelo,
            isaida.quantidade, 
            isaida.identificacao 
        FROM 
            db_sigem.itens_saida isaida
        INNER JOIN 
            db_sigem.item i ON isaida.item_id = i.id_item
        WHERE 
            isaida.termo_id = :termo_id
    ";
    $stmt_itens_saida = $pdo->prepare($sql_itens_saida);
    $stmt_itens_saida->execute([':termo_id' => $termo_id]);
    $itens_saida = $stmt_itens_saida->fetchAll(PDO::FETCH_ASSOC);

    // Retorna os dados em formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'termo' => $termo,
        'itens_saida' => $itens_saida
    ]);
} catch (Exception $e) {
    die(json_encode(["error" => "Erro ao buscar os dados: " . $e->getMessage()]));
}
